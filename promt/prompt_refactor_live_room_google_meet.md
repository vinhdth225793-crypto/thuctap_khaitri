# Prompt Refactor Live Room / Google Meet theo repo hiện tại

## Mục tiêu file này

File này dùng làm **prompt tổng hợp** để đưa cho AI coding assistant hoặc dev team nhằm:

- kiểm tra lại module **live room / phòng học trực tuyến** trong repo hiện tại
- sửa lỗi phần **Google Meet**
- bổ sung flow cho **admin vào cùng phòng**
- cho phép **giảng viên cập nhật link Google Meet mới**
- tách rõ **internal room** và **external provider room**
- sửa các lỗi room theo hướng **ít phá hệ thống cũ nhất**

---

## Prompt khung tổng quát

```text
Bạn đang làm việc trên một dự án Laravel quản lý trung tâm giáo dục.

Repo hiện đã có module live room / phòng học trực tuyến, gồm các thành phần chính:
- route cho giảng viên live-room
- route cho học viên live-room
- controller riêng cho giảng viên và học viên
- model PhongHocLive, PhongHocLiveNguoiThamGia, PhongHocLiveBanGhi
- service TeacherScheduleLiveRoomService, LiveRoomParticipationService, OnlineMeetingProviderService, LiveRoomPlatformService
- config live_room.php có khai báo internal, zoom, google_meet
- room hiện đang gắn với lịch học / bài giảng
- participant log, recording flow, attendance integration đã có ở mức cơ bản

Tuy nhiên hiện có các vấn đề nghiệp vụ và kỹ thuật sau:
1. Admin chưa thể vào cùng phòng Google Meet như giảng viên/học viên.
2. Giảng viên chưa có flow chuẩn để cập nhật link Google Meet mới cho một buổi học/phòng học.
3. Một số chức năng room bị lỗi do kiến trúc hiện tại đang nghiêng về internal room nhiều hơn external provider room.
4. Google Meet hiện nên được xử lý như external provider, không nên ép hoàn toàn theo lifecycle của internal room.
5. Cần giữ backward compatibility tối đa, không rewrite toàn bộ nếu chưa cần.

Yêu cầu triển khai chung:
- Phân tích hiện trạng route/controller/service/model/config hiện có.
- Tách rõ internal room và external provider room.
- Bổ sung admin supervisor access.
- Bổ sung chức năng giảng viên cập nhật link Google Meet mới.
- Chuẩn hóa state machine và access control cho room.
- Giữ participant log, attendance integration, recording flow nếu còn phù hợp.
- Viết migration/model/service/controller/request validation/test nếu cần.
- Mỗi phase phải có:
  1. phân tích hiện trạng
  2. vấn đề cần giải quyết
  3. thiết kế DB/domain
  4. file cần tạo/sửa
  5. code cụ thể
  6. backward compatibility strategy
  7. acceptance criteria
- Ưu tiên production-safe và rollout an toàn.
```

---

# PHASE 1 — Kiểm tra hiện trạng module live room

## Prompt

```text
Dựa trên codebase Laravel hiện tại, hãy thực hiện PHASE 1: kiểm tra hiện trạng module live room / Google Meet.

Mục tiêu:
- Xác định chính xác kiến trúc hiện có.
- Chỉ ra những chỗ đang lỗi hoặc thiếu flow.
- Chuẩn bị nền cho các phase refactor tiếp theo.

Yêu cầu:
1. Rà soát route hiện có cho:
   - giảng viên
   - học viên
   - admin
2. Rà soát controller hiện có:
   - show room
   - join room
   - leave room
   - start room
   - end room
   - recording
3. Rà soát model:
   - PhongHocLive
   - PhongHocLiveNguoiThamGia
   - PhongHocLiveBanGhi
4. Rà soát service:
   - TeacherScheduleLiveRoomService
   - LiveRoomParticipationService
   - OnlineMeetingProviderService
   - LiveRoomPlatformService
5. Rà soát config live_room.php và xác định:
   - internal đang hoạt động thế nào
   - google_meet đang hoạt động thế nào
   - zoom đang ở mức nào
6. Chỉ ra rõ:
   - admin hiện có route/controller/policy để vào room hay không
   - giảng viên hiện có flow cập nhật link Google Meet mới hay không
   - Google Meet hiện là link placeholder hay link quản lý thật
   - những chức năng room nào đang lệch giữa internal và external provider
7. Liệt kê bug/rủi ro theo nhóm:
   - access control
   - room state
   - provider integration
   - UI/UX mismatch

Kết quả trả về theo format:
1. Tổng quan kiến trúc hiện tại
2. Route và flow hiện có
3. Những vấn đề đang có
4. Danh sách điểm cần refactor
5. Danh sách file liên quan
6. Acceptance criteria cho phase phân tích
```

---

# PHASE 2 — Thêm flow cho admin vào phòng với vai trò giám sát

## Prompt

```text
Dựa trên codebase Laravel hiện tại, hãy triển khai PHASE 2: bổ sung flow cho admin vào phòng học trực tuyến với vai trò giám sát.

Mục tiêu nghiệp vụ:
- Admin có thể vào cùng phòng Google Meet hoặc phòng internal như giảng viên/học viên.
- Admin không phải là chủ phòng, nhưng có quyền supervisory access.
- Hệ thống vẫn ghi participant log đầy đủ.

Yêu cầu:
1. Phân tích hiện trạng route/controller/policy hiện có cho live room.
2. Thiết kế bổ sung flow admin:
   - admin.live-room.show
   - admin.live-room.join
   - admin.live-room.leave
3. Thiết kế role participant mới nếu cần, ví dụ:
   - admin_supervisor
4. Cập nhật LiveRoomParticipationService hoặc service tương đương để hỗ trợ role admin.
5. Thiết kế policy/access rule để admin được vào room mà không làm thay đổi ownership của room.
6. Nếu room là Google Meet:
   - admin được redirect ra external_meeting_url
   - participant log được ghi local
7. Nếu room là internal:
   - admin có thể vào như supervisor hoặc observer
8. Viết migration nếu cần mở rộng role/status trong participant log.
9. Viết test cases cho:
   - admin xem room
   - admin join room
   - admin leave room
   - admin không phải owner nhưng vẫn vào được
10. Liệt kê file cần tạo/sửa.

Kết quả trả về theo format:
1. Phân tích hiện trạng
2. Thiết kế DB/domain
3. Route/controller/policy mới
4. Service cập nhật
5. Test cases
6. Backward compatibility
7. Acceptance criteria
```

---

# PHASE 3 — Cho giảng viên cập nhật link Google Meet mới

## Prompt

```text
Dựa trên codebase Laravel hiện tại, hãy triển khai PHASE 3: cho phép giảng viên cập nhật link Google Meet mới cho phòng học / lịch học.

Mục tiêu nghiệp vụ:
- Giảng viên có thể thay link Google Meet mới khi link cũ hỏng hoặc đổi lịch.
- Hệ thống phải lưu lịch sử thay đổi link.
- Sau khi đổi link, học viên và admin nhìn thấy link mới đúng ngay.

Yêu cầu:
1. Phân tích hiện trạng `OnlineMeetingProviderService`, `TeacherScheduleLiveRoomService`, `PhongHocLive`, `LichHoc` và các field đang lưu link online.
2. Thiết kế chức năng cập nhật link mới cho giảng viên:
   - form/API cập nhật link
   - validate link Google Meet hợp lệ
   - chọn phạm vi áp dụng nếu cần (chỉ buổi này hoặc series nếu có)
3. Khi cập nhật link, hệ thống phải update đồng bộ các nơi phù hợp, ví dụ:
   - lich_hoc.link_online
   - online_link_source
   - phong_hoc_live.external_meeting_url hoặc field tương đương
   - payload provider nếu có
4. Thiết kế bảng history/audit ví dụ:
   - live_room_link_histories
5. Mỗi lần đổi link phải lưu:
   - link cũ
   - link mới
   - người sửa
   - thời gian sửa
   - lý do sửa
6. Nếu room đã công bố cho học viên, cần đề xuất cơ chế thông báo hoặc ít nhất cập nhật read model/UI đúng.
7. Viết request validation cho link Google Meet.
8. Viết test cases cho:
   - đổi link hợp lệ
   - đổi link sai format
   - đổi link nhiều lần
   - xem history
9. Liệt kê file cần tạo/sửa.

Kết quả trả về theo format:
1. Phân tích hiện trạng
2. Thiết kế DB/domain
3. API/form và validation
4. Service xử lý cập nhật link
5. Audit/history
6. Test cases
7. Backward compatibility
8. Acceptance criteria
```

---

# PHASE 4 — Tách rõ internal room và external provider room

## Prompt

```text
Dựa trên codebase Laravel hiện tại, hãy triển khai PHASE 4: tách rõ internal room và external provider room (Google Meet / Zoom).

Mục tiêu nghiệp vụ:
- Internal room và Google Meet phải có lifecycle khác nhau.
- Không ép Google Meet đi theo toàn bộ logic start/join/end kiểu internal room.
- Code dễ hiểu hơn, bớt lỗi hơn.

Yêu cầu:
1. Phân tích hiện trạng `TeacherScheduleLiveRoomService::ensureInternalRoom()` và các chỗ đang ép flow về internal room.
2. Thiết kế field hoặc enum rõ ràng cho platform type, ví dụ:
   - internal
   - google_meet
   - zoom
3. Nếu cần, bổ sung migration cho các field như:
   - platform_type
   - external_meeting_url
   - external_meeting_code
   - external_link_updated_at
   - external_link_updated_by
4. Thiết kế service mới hoặc refactor service cũ để xử lý theo strategy pattern:
   - InternalRoomLifecycleService
   - ExternalMeetingLifecycleService
   hoặc naming tương đương
5. Với internal room:
   - giữ flow create/start/join/end hiện có nếu phù hợp
6. Với Google Meet:
   - không provision internal player
   - join là redirect ra external link sau khi check quyền và ghi log local
   - end chỉ kết thúc trạng thái local, không cố điều khiển cuộc họp Google Meet thật
7. Với Zoom:
   - chuẩn bị khung tương tự Google Meet nếu hiện chưa tích hợp API thật
8. Viết test cases cho:
   - room internal
   - room google_meet
   - room zoom placeholder
9. Liệt kê file cần tạo/sửa.

Kết quả trả về theo format:
1. Phân tích hiện trạng
2. Thiết kế domain mới
3. Migration/model thay đổi
4. Service strategy
5. Controller/refactor flow
6. Test cases
7. Backward compatibility
8. Acceptance criteria
```

---

# PHASE 5 — Chuẩn hóa flow join/start/leave/end theo từng nền tảng

## Prompt

```text
Dựa trên codebase Laravel hiện tại, hãy triển khai PHASE 5: chuẩn hóa flow join/start/leave/end room theo từng nền tảng.

Mục tiêu nghiệp vụ:
- Room phải hoạt động ổn định hơn.
- Logic hành động phụ thuộc platform_type.
- Hạn chế bug sai trạng thái và sai redirect.

Yêu cầu:
1. Thiết kế lại flow cho từng actor:
   - giảng viên
   - học viên
   - admin
2. Thiết kế lại flow theo platform:
   - internal
   - google_meet
   - zoom
3. Với internal:
   - teacher start -> live
   - teacher join -> player/host
   - student join -> participant
   - teacher end -> ended
4. Với Google Meet:
   - teacher/admin/student join -> check access -> ghi participant log -> redirect external URL
   - leave -> chỉ cập nhật local log
   - end -> cập nhật trạng thái local
5. Thiết kế service trung gian ví dụ:
   - LiveRoomAccessService
   - LiveRoomStateService
   - LiveRoomJoinRedirectService
6. Cập nhật controller để mỏng hơn, chỉ orchestration qua service.
7. Chuẩn hóa participant role và status.
8. Viết test cases theo actor + platform.
9. Liệt kê file cần tạo/sửa.

Kết quả trả về theo format:
1. Phân tích flow hiện tại
2. Flow mới theo actor
3. Flow mới theo platform
4. Service/refactor controller
5. Test cases
6. Backward compatibility
7. Acceptance criteria
```

---

# PHASE 6 — Chuẩn hóa trạng thái room và hint hiển thị

## Prompt

```text
Dựa trên codebase Laravel hiện tại, hãy triển khai PHASE 6: chuẩn hóa state machine của room và các hint hiển thị.

Mục tiêu nghiệp vụ:
- Room không bị sai trạng thái.
- UI có thể hiển thị đúng: chưa đến giờ, sắp bắt đầu, đang diễn ra, đã kết thúc, đã hủy.
- Trạng thái phải hợp lý cho cả internal room và Google Meet.

Yêu cầu:
1. Phân tích các field trạng thái hiện có trong `PhongHocLive`.
2. Thiết kế state machine chuẩn, ví dụ:
   - draft
   - scheduled
   - ready
   - live
   - ended
   - cancelled
3. Thiết kế rule chuyển trạng thái khác nhau giữa:
   - internal room
   - google_meet room
4. Cập nhật accessor/hint hiển thị nếu cần.
5. Cập nhật service để tránh chuyển trạng thái sai.
6. Viết test cases cho state transition hợp lệ và không hợp lệ.
7. Liệt kê file cần tạo/sửa.

Kết quả trả về theo format:
1. Phân tích hiện trạng
2. State machine đề xuất
3. Rule chuyển trạng thái
4. Code refactor model/service
5. Test cases
6. Backward compatibility
7. Acceptance criteria
```

---

# PHASE 7 — Sửa room bug theo hướng an toàn, ít phá code cũ

## Prompt

```text
Dựa trên codebase Laravel hiện tại, hãy triển khai PHASE 7: sửa các bug live room theo hướng an toàn và ít phá code cũ nhất.

Mục tiêu:
- Sửa các lỗi room đang có.
- Không đập bỏ toàn bộ flow cũ.
- Bổ sung lớp adapter/fallback nếu cần.

Yêu cầu:
1. Rà soát các bug/risk đã xác định ở các phase trước.
2. Đề xuất chiến lược sửa theo hướng:
   - giữ model/bảng cũ nếu còn dùng được
   - thêm field mới thay vì xóa field cũ ngay
   - controller cũ gọi service mới
   - fallback mapping cho dữ liệu cũ chưa có platform_type rõ ràng
3. Nếu cần, tạo adapter để map:
   - old internal-first flow
   - new platform-aware flow
4. Viết migration tối thiểu.
5. Viết feature flag hoặc config switch nếu cần.
6. Viết regression tests cho các chức năng cũ:
   - teacher show/join/start/end
   - student show/join/leave
   - recording add/delete
7. Liệt kê file cần tạo/sửa.
8. Đề xuất rollout plan an toàn.

Kết quả trả về theo format:
1. Danh sách bug/risk
2. Chiến lược sửa an toàn
3. Migration tối thiểu
4. Adapter/fallback
5. Regression tests
6. Rollout plan
7. Acceptance criteria
```

---

# Prompt master chạy toàn bộ theo phase

```text
Tôi có một dự án Laravel quản lý trung tâm giáo dục, trong đó đã có module live room / phòng học trực tuyến.

Hiện trạng:
- Có route/controller/model/service cho live room.
- Có participant log, recording flow, attendance integration cơ bản.
- Có config internal / google_meet / zoom.
- Room gắn với lịch học / bài giảng.

Vấn đề hiện tại:
1. Admin chưa thể vào cùng phòng Google Meet như giảng viên/học viên.
2. Giảng viên chưa thể cập nhật link Google Meet mới một cách chuẩn hóa.
3. Một số chức năng room bị lỗi.
4. Kiến trúc hiện tại đang nghiêng nhiều về internal room và chưa tách rõ external provider room.

Mục tiêu refactor:
- Bổ sung admin supervisor access.
- Bổ sung flow cập nhật link Google Meet mới.
- Tách internal room và external provider room.
- Chuẩn hóa join/start/leave/end theo platform.
- Chuẩn hóa state machine room.
- Sửa bug theo hướng ít phá code cũ nhất.
- Giữ backward compatibility và rollout an toàn.

Hãy chia công việc thành từng phase, và với mỗi phase phải đưa ra:
1. Mục tiêu
2. Phân tích hiện trạng
3. Vấn đề cần giải quyết
4. Thiết kế DB/domain
5. File cần tạo/sửa
6. Code migration/model/service/controller/request/test
7. Backward compatibility strategy
8. Rollout plan
9. Acceptance criteria

Hãy bắt đầu bằng:
A. Phân tích kiến trúc live room hiện tại
B. Đề xuất kiến trúc mục tiêu
C. Chia phase hợp lý
D. Triển khai Phase 1 trước với code cụ thể
```

---

# Gợi ý cách dùng file này

## Cách 1 — Dùng theo phase
- copy từng prompt phase
- đưa vào Cursor / Claude / ChatGPT / Copilot
- review kết quả theo phase

## Cách 2 — Dùng prompt master
- dùng khi muốn AI tự chia phase và code dần
- phù hợp nếu bạn muốn AI vừa phân tích vừa đề xuất kiến trúc

## Cách 3 — Dùng làm spec cho dev team
- bóc từng phase thành task
- tạo issue theo phase
- review từng acceptance criteria trước khi merge

---

# Kết quả mong muốn sau khi hoàn thành toàn bộ

Sau khi hoàn thành các phase, hệ thống live room nên đạt được:

1. **Admin vào được room** với vai trò giám sát.
2. **Giảng viên cập nhật được link Google Meet mới** và có audit log.
3. **Google Meet được xử lý đúng là external provider**, không bị ép theo internal room lifecycle.
4. **Flow join/start/leave/end rõ ràng theo từng platform**.
5. **Room state rõ ràng, ít bug hơn**.
6. **Participant log, attendance integration, recording flow** vẫn hoạt động hoặc được cải thiện.
7. **Backward compatibility** được giữ ở mức an toàn.
8. Có thể rollout dần mà không phá chức năng hiện đang chạy.
