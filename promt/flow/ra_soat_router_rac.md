# Bối cảnh
Bạn là một coding agent đang làm việc trực tiếp trên codebase Laravel của tôi.

Repo hiện đang đăng ký web routing qua:
- `bootstrap/app.php`
- `routes/web.php`
- `routes/console.php`

Ghi nhận ban đầu:
- `bootstrap/app.php` đang dùng `withRouting(web: __DIR__.'/../routes/web.php', commands: __DIR__.'/../routes/console.php', health: '/up')`
- Không thấy `app/Providers/RouteServiceProvider.php` đang được dùng để map route web riêng
- `php artisan route:list` hiện cho khoảng `235` routes

Mục tiêu của bạn là:
- đọc toàn bộ hệ thống routing hiện tại
- rà soát route nào còn được dùng thật
- xác định route nào dư thừa, trùng lặp, bị shadow, chỉ còn là alias cũ, hoặc hoàn toàn không còn công dụng
- chỉ xóa route khi đã có bằng chứng rõ ràng là an toàn
- sau đó cập nhật tham chiếu liên quan và xác minh hệ thống vẫn chạy đúng

KHÔNG được xóa route theo cảm tính.
KHÔNG được thấy “có vẻ cũ” là xóa luôn.
KHÔNG được làm hỏng backward compatibility nếu route alias cũ vẫn còn được dùng.


# Phạm vi bắt buộc phải đọc
Trước khi sửa, bạn phải đọc và đối chiếu ít nhất các khu vực sau:

1. Routing core
- `bootstrap/app.php`
- `routes/web.php`
- `routes/console.php`

2. Tất cả nơi có thể tham chiếu route
- `app/Http/Controllers`
- `resources/views`
- `resources/views/components`
- `resources/views/layouts`
- `app/Services`
- `tests/Feature`
- `tests/Unit`

3. Tìm cả tham chiếu gián tiếp
- `route('...')`
- `to_route('...')`
- `redirect()->route(...)`
- `redirect()->to(...)`
- `url(...)`
- hardcoded URL như `'/admin/...', '/giang-vien/...', '/hoc-vien/...'`
- form `action=""`
- JS `fetch(...)`, `axios(...)`, `window.location`, data attributes chứa path
- test `assertRedirect`, `get`, `post`, `put`, `delete`, `patch`, `visit`, `assertSee(route(...))`


# Mục tiêu đầu ra
Bạn phải làm 4 việc:

1. Lập danh sách route
- route name
- method
- URI
- controller/closure/redirect target
- middleware

2. Phân loại từng route vào một trong các nhóm
- `Đang dùng rõ ràng`
- `Alias/redirect cũ nhưng còn giá trị`
- `Trùng lặp / shadow / có thể gộp`
- `Nghi route rác / không còn công dụng`

3. Chỉ xóa route nếu đạt đủ điều kiện an toàn
- không còn tham chiếu trong code, view, JS, test
- hoặc là route trùng hoàn toàn với route khác và route kia đã là canonical
- hoặc là route placeholder/alias cũ không còn ai dùng
- hoặc là route bị định nghĩa thừa và bị route khác cùng method + URI ghi đè

4. Sau khi xóa
- cập nhật toàn bộ tham chiếu liên quan nếu cần
- cập nhật test bị ảnh hưởng nếu hợp lý
- chạy kiểm tra lại


# Những cụm route cần ưu tiên nghi ngờ và kiểm tra kỹ
Đây là các điểm nghi vấn ban đầu, nhưng chưa được phép xóa ngay nếu chưa có bằng chứng:

1. Banner admin bị lặp
- `admin/banner/*`
- `admin/settings/banners/*`

2. Route giảng viên bài giảng có dấu hiệu khai báo lặp
- có route standalone `GET /giang-vien/bai-giang`
- và lại có group `prefix('bai-giang')`

3. Alias cũ của giảng viên
- `/giang-vien/phan-cong`
- `/giang-vien/phan-cong/{id}/xac-nhan`

4. Placeholder / backward compatibility routes
- `/giang-vien/tao-bai-giang`
- `/giang-vien/tao-bai-kiem-tra`
- `/giang-vien/cham-diem`
- `/admin/kiem-tra-online` closure redirect
- `/home` redirect closure
- `/profile` redirect closure

5. Route name/prefix legacy dễ gây nhầm
- `prefix('nhom-nganh')->name('mon-hoc.')`

Bạn phải kiểm tra thật kỹ xem chúng còn đang được dùng ở view, menu, test, redirect hay JS hay không.


# Cách làm bắt buộc
Làm theo đúng các phase sau.

## Phase 1: Audit route và reference map
Thực hiện:
- chạy `php artisan route:list`
- nếu cần, lọc thêm theo path/name
- scan codebase để map mọi chỗ gọi route

Bạn phải tạo được bảng như sau cho từng candidate:
- Route
- Dùng ở đâu
- Bao nhiêu tham chiếu
- Có route thay thế không
- Nếu xóa thì cần sửa file nào
- Mức rủi ro

Chưa xóa ngay ở phase này.

## Phase 2: Chốt danh sách route có thể xóa an toàn
Chỉ chọn các route có bằng chứng mạnh:
- không có tham chiếu
- hoặc bị trùng / bị override
- hoặc chỉ là alias cũ và toàn bộ code đã dùng route mới

Nếu có route “có thể cũ” nhưng còn 1-2 tham chiếu mơ hồ, chưa được phép xóa.

## Phase 3: Xóa route và cập nhật tham chiếu
Khi sửa:
- ưu tiên route canonical duy nhất
- xóa route thừa trong `routes/web.php`
- sửa các tham chiếu cũ còn sót
- không đổi business logic controller nếu không thật sự cần
- không đổi middleware/permission ngoài mục tiêu route cleanup

## Phase 4: Verify
Bắt buộc chạy:
- `php artisan route:list`
- `php artisan test`

Nếu có route quan trọng bị mất hoặc redirect hỏng, phải rollback phần đó bằng cách sửa lại ngay.


# Quy tắc an toàn
1. Không được xóa các route auth/public cốt lõi nếu chưa chứng minh là dư:
- `/`
- `/search`
- `/dang-ky`
- `/dang-nhap`
- `/dang-xuat`
- `/profile`
- `/home`
- `/thong-bao/*`
- health route `/up`

2. Không được xóa route chỉ vì:
- tên cũ
- comment ghi “backward compatibility”
- là redirect
- là closure

3. Nếu một route hiện tại không được gọi trực tiếp trong Blade/controller nhưng vẫn được test hoặc được JS dùng, phải coi là đang dùng.

4. Nếu hai route cùng chức năng nhưng route cũ vẫn còn reference, ưu tiên:
- migrate toàn bộ reference sang route mới
- sau đó mới xóa route cũ

5. Nếu route bị trùng method + URI, phải xác định route nào đang có hiệu lực thực sự trong Laravel trước khi xóa.


# Từ khóa tìm kiếm gợi ý
Bạn có thể dùng các lệnh kiểu:

```powershell
php artisan route:list
```

```powershell
rg -n "route\\(|to_route\\(|redirect\\(\\)->route|url\\(|action\\(" app resources/views tests
```

```powershell
rg -n "'/(admin|giang-vien|hoc-vien|thong-bao|home|profile)|\"/(admin|giang-vien|hoc-vien|thong-bao|home|profile)" app resources/views tests
```

```powershell
rg -n "fetch\\(|axios\\(|window\\.location|location\\.href|data-url|data-route|action=" resources/views app tests
```


# Yêu cầu báo cáo
Trước khi xóa route hàng loạt, phải báo cáo:

1. Tổng số route hiện tại
2. Các nhóm route nghi ngờ dư thừa
3. Các route đề xuất xóa
4. Bằng chứng cho từng route
5. Rủi ro nếu xóa

Sau khi xóa xong, phải báo cáo:

1. Những route đã xóa
2. Những file đã cập nhật tham chiếu
3. Những route nghi ngờ nhưng chưa dám xóa
4. Kết quả `php artisan route:list`
5. Kết quả `php artisan test`


# Kỳ vọng cuối cùng
Kết quả tốt là:
- file `routes/web.php` gọn hơn
- không còn route duplicate/alias rác không cần thiết
- các route legacy không dùng nữa được dọn
- không làm hỏng link, redirect, form submit, ajax, test
- hệ thống vẫn pass test sau cleanup


# Bắt đầu công việc
Bây giờ hãy:

1. đọc `bootstrap/app.php`
2. đọc `routes/web.php`
3. chạy `php artisan route:list`
4. quét tất cả tham chiếu route trong controller/view/js/test
5. lập danh sách candidate route rác
6. chỉ sau khi có bằng chứng mới tiến hành xóa
7. chạy verify lại toàn hệ thống
