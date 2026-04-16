Tạo file XML hoàn chỉnh để import trực tiếp vào draw.io (diagrams.net) cho sơ đồ UML Use Case của hệ thống LEARNTEST ONLINE - CỔNG HỌC TẬP VÀ KIỂM TRA ONLINE.

Yêu cầu bắt buộc:
- Chỉ trả về XML thuần, không giải thích, không markdown, không dùng dấu ```
- XML phải tương thích draw.io, có cấu trúc mxfile, diagram, mxGraphModel, root, mxCell
- Có system boundary tên: LEARNTEST ONLINE - CỔNG HỌC TẬP VÀ KIỂM TRA ONLINE
- Actor đặt ngoài system boundary
- Use case dùng hình ellipse
- Có đường nối association giữa actor và use case
- Có thể dùng <<include>> cho các quan hệ bắt buộc
- Bố cục rõ ràng, không chồng chéo
- Dùng tiếng Việt cho toàn bộ actor và use case
- Chỉ xuất XML bắt đầu bằng <mxfile>

Hãy vẽ SƠ ĐỒ USE CASE TỔNG QUÁT dựa trên các actor và nhóm use case sau:

ACTOR CHÍNH:
1. Khách vãng lai
2. Quản trị viên
3. Giảng viên
4. Học viên

ACTOR PHỤ:
5. Dịch vụ phòng học trực tuyến

8 USE CASE TỔNG QUÁT:
1. Xác thực và quản lý tài khoản
2. Quản lý người dùng
3. Quản lý cấu trúc đào tạo
4. Quản lý lớp học và lịch học
5. Quản lý nội dung học tập
6. Quản lý kiểm tra và đánh giá
7. Theo dõi kết quả và tiến độ học tập
8. Quản trị và cấu hình hệ thống

LIÊN KẾT ACTOR - USE CASE TỔNG QUÁT:
- Khách vãng lai: Xác thực và quản lý tài khoản
- Quản trị viên: Xác thực và quản lý tài khoản; Quản lý người dùng; Quản lý cấu trúc đào tạo; Quản lý lớp học và lịch học; Quản lý nội dung học tập; Quản lý kiểm tra và đánh giá; Theo dõi kết quả và tiến độ học tập; Quản trị và cấu hình hệ thống
- Giảng viên: Xác thực và quản lý tài khoản; Quản lý lớp học và lịch học; Quản lý nội dung học tập; Quản lý kiểm tra và đánh giá; Theo dõi kết quả và tiến độ học tập
- Học viên: Xác thực và quản lý tài khoản; Quản lý lớp học và lịch học; Quản lý nội dung học tập; Quản lý kiểm tra và đánh giá; Theo dõi kết quả và tiến độ học tập
- Dịch vụ phòng học trực tuyến: Quản lý nội dung học tập

YÊU CẦU BỐ CỤC:
- Bên trái: Khách vãng lai, Quản trị viên
- Bên phải: Giảng viên, Học viên, Dịch vụ phòng học trực tuyến
- 8 use case tổng quát đặt trong system boundary theo dạng lưới 2 cột hoặc theo chiều dọc rõ ràng
- Khoảng cách đều, không đè lên nhau

CHỈ TRẢ VỀ XML DRAW.IO HỢP LỆ.