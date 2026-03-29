Bạn là senior Laravel developer và database refactor engineer.

Bối cảnh hiện tại:
- Bạn đã audit và refactor bộ migration của project Laravel hệ thống học tập và kiểm tra online.
- Hiện tại bạn đã gộp 57 migration cũ thành khoảng 7 migration lớn.
- Tuy nhiên cách gộp này vẫn chưa tối ưu vì mỗi migration đang quá lớn, ôm nhiều bảng/chức năng trong cùng 1 file, gây khó đọc, khó kiểm tra, khó bảo trì và khó debug.
- Tôi muốn bạn tiếp tục refactor lại bộ migration mới này theo hướng chia nhỏ hơn, bám theo từng chức năng của hệ thống.

MỤC TIÊU MỚI:
1. Không để migration quá lớn, quá nhiều bảng trong 1 file.
2. Chia migration theo từng chức năng nhỏ, dễ đọc, dễ bảo trì.
3. Tên migration phải dễ nhìn, dễ hiểu, ưu tiên theo tiếng Việt.
4. Tuy nhiên để đảm bảo tương thích kỹ thuật trong Laravel, filesystem, terminal, git, hãy đặt tên file migration bằng tiếng Việt KHÔNG DẤU, viết thường, dùng dấu gạch dưới.
5. Không thay đổi schema cuối cùng đã chốt nếu không thật sự cần thiết.
6. Không làm gãy hệ thống hiện tại.
7. Giữ nguyên tên bảng và tên cột cuối cùng mà code hiện tại đang dùng, trừ khi phát hiện lỗi thật sự nghiêm trọng và có giải thích rõ.

NGUYÊN TẮC ĐẶT TÊN FILE MIGRATION:
- Dùng tiếng Việt không dấu.
- Dùng mô tả rõ chức năng.
- Viết thường toàn bộ.
- Dùng dấu gạch dưới.
- Mỗi migration chỉ nên phục vụ 1 bảng hoặc 1 nhóm rất nhỏ có liên quan trực tiếp.
- Không gom quá nhiều domain vào 1 migration.

Ví dụ tên đúng:
- tao_bang_nguoi_dung
- tao_bang_giang_vien
- tao_bang_hoc_vien
- tao_bang_tai_khoan_cho_phe_duyet
- tao_bang_nhom_nganh
- tao_bang_khoa_hoc
- tao_bang_module_hoc
- tao_bang_hoc_vien_khoa_hoc
- tao_bang_lich_hoc
- tao_bang_diem_danh
- tao_bang_tai_nguyen_buoi_hoc
- tao_bang_bai_kiem_tra
- tao_bang_ngan_hang_cau_hoi
- tao_bang_dap_an_cau_hoi
- tao_bang_ket_qua_bai_kiem_tra
- tao_bang_phong_hoc_truc_tuyen

Không dùng kiểu tên quá tổng:
- create_training_catalog_tables
- create_learning_resource_tables
- create_assessment_tables

===================================
1. YÊU CẦU REFACTOR LẠI MIGRATION
===================================

Hãy refactor lại bộ migration mới hiện tại theo hướng chia nhỏ theo từng chức năng thực tế của hệ thống.

Chia theo các nhóm chức năng sau:

A. NHÓM NỀN TẢNG HỆ THỐNG
- cache
- jobs
- failed_jobs
- password_reset_tokens
- sessions

B. NHÓM NGƯỜI DÙNG VÀ PHÂN QUYỀN
- nguoi_dung
- giang_vien
- hoc_vien
- tai_khoan_cho_phe_duyet

C. NHÓM DANH MỤC ĐÀO TẠO
- nhom_nganh
- khoa_hoc
- module_hoc

D. NHÓM TRIỂN KHAI GIẢNG DẠY
- phân công giảng viên nếu có bảng riêng
- hoc_vien_khoa_hoc
- lich_hoc
- diem_danh
- yeu_cau_hoc_vien

E. NHÓM TÀI NGUYÊN HỌC TẬP
- tai_nguyen_buoi_hoc
- các bảng liên quan thư viện nếu thực sự đang có trong code

F. NHÓM KIỂM TRA ĐÁNH GIÁ
- bai_kiem_tra nếu có
- ngan_hang_cau_hoi
- dap_an_cau_hoi
- ket_qua_bai_kiem_tra nếu có
- bảng trung gian liên quan nếu có

G. NHÓM HỌC TRỰC TUYẾN / LIVE ROOM
- phong_hoc_truc_tuyen
- lich_su_phong_hoc nếu có
- bảng liên quan live nếu có

===================================
2. NGUYÊN TẮC CHIA NHỎ
===================================

Hãy áp dụng các nguyên tắc này:

1. Mỗi migration ưu tiên chỉ tạo 1 bảng.
2. Chỉ gộp 2 bảng vào 1 migration nếu:
   - quan hệ cực kỳ chặt
   - cùng một chức năng nhỏ
   - tách ra không mang lại lợi ích rõ ràng
3. Những bảng cốt lõi như:
   - nguoi_dung
   - giang_vien
   - hoc_vien
   - nhom_nganh
   - khoa_hoc
   - module_hoc
   - hoc_vien_khoa_hoc
   - lich_hoc
   - diem_danh
   - tai_nguyen_buoi_hoc
   - ngan_hang_cau_hoi
   - dap_an_cau_hoi
   phải tách riêng migration nếu hợp lý.
4. Các bảng framework của Laravel có thể gộp ít file hơn nếu cần, nhưng vẫn phải rõ ràng.
5. Các foreign key phải được sắp xếp đúng thứ tự migration để migrate từ đầu không lỗi.

===================================
3. VIỆC CẦN LÀM
===================================

Hãy thực hiện lần lượt:

BƯỚC 1
- Đọc lại bộ migration mới hiện tại.
- Liệt kê migration nào đang quá lớn.
- Chỉ ra vì sao nó quá lớn hoặc khó bảo trì.

BƯỚC 2
- Đề xuất danh sách migration mới đã chia nhỏ hơn.
- Với mỗi migration, nêu:
  - tên file migration
  - bảng nào được tạo
  - lý do tách riêng

BƯỚC 3
- Refactor code migration theo danh sách mới.
- Đảm bảo schema cuối cùng không bị thay đổi ngoài ý muốn.
- Đảm bảo giữ nguyên tên bảng và tên cột đang được code sử dụng.

BƯỚC 4
- Kiểm tra lại thứ tự migration.
- Đảm bảo migrate:fresh chạy được từ đầu.
- Không để lỗi foreign key do sai thứ tự.

BƯỚC 5
- Cập nhật lại báo cáo tóm tắt sau khi chia nhỏ migration.

===================================
4. QUY TẮC KỸ THUẬT RẤT QUAN TRỌNG
===================================

- KHÔNG đổi schema cuối cùng nếu không có lý do rất rõ.
- KHÔNG đổi tên bảng/cột đang được code dùng.
- KHÔNG gom lại thành các migration kiểu domain quá to.
- KHÔNG đặt tên file migration bằng tiếng Anh tổng quát nếu có thể diễn đạt bằng tiếng Việt không dấu rõ hơn.
- KHÔNG dùng tiếng Việt có dấu trong tên file.
- KHÔNG đụng vào dữ liệu local thật của tôi.
- Chỉ sửa code migration, model, quan hệ hoặc báo cáo nếu thật sự cần.

===================================
5. CÁCH ĐẶT TÊN MIGRATION MONG MUỐN
===================================

Hãy ưu tiên format như sau:

YYYY_MM_DD_HHMMSS_tao_bang_ten_chuc_nang.php

Ví dụ:
- 2026_03_28_000001_tao_bang_nguoi_dung.php
- 2026_03_28_000002_tao_bang_giang_vien.php
- 2026_03_28_000003_tao_bang_hoc_vien.php
- 2026_03_28_000004_tao_bang_tai_khoan_cho_phe_duyet.php
- 2026_03_28_000005_tao_bang_nhom_nganh.php
- 2026_03_28_000006_tao_bang_khoa_hoc.php
- 2026_03_28_000007_tao_bang_module_hoc.php
- 2026_03_28_000008_tao_bang_hoc_vien_khoa_hoc.php
- 2026_03_28_000009_tao_bang_lich_hoc.php
- 2026_03_28_000010_tao_bang_diem_danh.php
- 2026_03_28_000011_tao_bang_tai_nguyen_buoi_hoc.php
- 2026_03_28_000012_tao_bang_bai_kiem_tra.php
- 2026_03_28_000013_tao_bang_ngan_hang_cau_hoi.php
- 2026_03_28_000014_tao_bang_dap_an_cau_hoi.php
- 2026_03_28_000015_tao_bang_ket_qua_bai_kiem_tra.php

Nếu có bảng không tồn tại thật trong code hiện tại thì không được tự thêm chỉ vì ví dụ trên.

===================================
6. ĐẦU RA TÔI MUỐN
===================================

Kết quả cần gồm:

PHẦN A
- danh sách migration cũ đang quá lớn
- vì sao cần tách nhỏ

PHẦN B
- danh sách migration mới đề xuất sau khi chia nhỏ
- tên migration bằng tiếng Việt không dấu
- bảng tương ứng của từng migration

PHẦN C
- code migration đã refactor lại

PHẦN D
- xác nhận:
  - schema cuối cùng có giữ nguyên không
  - bảng/cột nào có thay đổi nếu có
  - thứ tự migration đã an toàn chưa

PHẦN E
- checklist test:
  - php artisan migrate:fresh
  - php artisan test
  - kiểm tra foreign key
  - kiểm tra model relation liên quan

===================================
7. MỤC TIÊU CUỐI CÙNG
===================================

Tôi muốn bộ migration:
- chia nhỏ theo chức năng
- dễ đọc
- dễ bảo trì
- dễ debug
- nhìn tên file là hiểu nó tạo bảng gì
- dùng tiếng Việt không dấu trong tên file
- vẫn bám đúng schema và code hiện tại của hệ thống học tập và kiểm tra online

Hãy bắt đầu bằng việc phân tích bộ migration mới hiện tại đang quá lớn ở đâu, rồi đề xuất danh sách migration chia nhỏ trước, sau đó mới thực hiện refactor code.