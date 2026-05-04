# Class Diagram Starter

Tai lieu nay gom 4 phan:
- Overview toan he thong
- Nhom Nguoi dung - Dao tao
- Nhom Noi dung - Live room
- Nhom Kiem tra - Ket qua

Muc tieu la ve so do class de doc kien truc domain. Vi vay chi nen giu:
- Ten class
- Mot vai thuoc tinh quan trong
- Quan he 1-1, 1-n, n-n

Khong nen dua het accessor/scope/service helper vao class diagram, neu khong so do se rat roi.

## 1. Overview

```mermaid
classDiagram
direction LR

class NguoiDung {
  +ma_nguoi_dung: int
  +ho_ten: string
  +email: string
  +vai_tro: string
}

class HocVien {
  +id: int
  +nguoi_dung_id: int
  +ma_hoc_vien: string
}

class GiangVien {
  +id: int
  +nguoi_dung_id: int
  +chuyen_nganh: string
  +so_gio_day: decimal
}

class NhomNganh {
  +id: int
  +ma_nhom_nganh: string
  +ten_nhom_nganh: string
}

class KhoaHoc {
  +id: int
  +ma_khoa_hoc: string
  +ten_khoa_hoc: string
  +loai: string
  +trang_thai_van_hanh: string
}

class LopHoc {
  +id: int
  +khoa_hoc_id: int
  +ma_lop_hoc: string
  +trang_thai_van_hanh: string
}

class ModuleHoc {
  +id: int
  +khoa_hoc_id: int
  +ma_module: string
  +ten_module: string
  +so_buoi: int
}

class LichHoc {
  +id: int
  +khoa_hoc_id: int
  +lop_hoc_id: int
  +module_hoc_id: int
  +giang_vien_id: int
  +ngay_hoc: date
  +hinh_thuc: string
  +trang_thai: string
}

class HocVienKhoaHoc {
  +id: int
  +khoa_hoc_id: int
  +hoc_vien_id: int
  +trang_thai: string
}

class PhanCongModuleGiangVien {
  +id: int
  +khoa_hoc_id: int
  +module_hoc_id: int
  +giang_vien_id: int
  +trang_thai: string
}

class BaiGiang
class TaiNguyenBuoiHoc
class PhongHocLive
class BaiKiemTra
class NganHangCauHoi
class BaiLamBaiKiemTra
class KetQuaHocTap
class PhieuXetDuyetKetQua

NguoiDung "1" --> "0..1" HocVien : profile
NguoiDung "1" --> "0..1" GiangVien : profile
NhomNganh "1" --> "*" KhoaHoc : quan_ly
KhoaHoc "1" --> "*" LopHoc : mo_lop
KhoaHoc "1" --> "*" ModuleHoc : gom
KhoaHoc "1" --> "*" LichHoc : len_lich
LopHoc "1" --> "*" LichHoc : chua
ModuleHoc "1" --> "*" LichHoc : ke_hoach
HocVien "1" --> "*" HocVienKhoaHoc : dang_ky
KhoaHoc "1" --> "*" HocVienKhoaHoc : ghi_danh
GiangVien "1" --> "*" PhanCongModuleGiangVien : duoc_phan_cong
KhoaHoc "1" --> "*" PhanCongModuleGiangVien : assignment
ModuleHoc "1" --> "*" PhanCongModuleGiangVien : assignment
LichHoc "*" --> "0..1" GiangVien : day
LichHoc "1" --> "*" BaiGiang : noi_dung
LichHoc "1" --> "*" TaiNguyenBuoiHoc : tai_nguyen
LichHoc "1" --> "*" BaiKiemTra : kiem_tra
BaiGiang "1" --> "0..1" PhongHocLive : live_room
BaiKiemTra "1" --> "*" BaiLamBaiKiemTra : bai_lam
BaiKiemTra "*" --> "*" NganHangCauHoi : ra_de
KhoaHoc "1" --> "*" KetQuaHocTap : tong_hop
KhoaHoc "1" --> "*" PhieuXetDuyetKetQua : phe_duyet
```

## 2. Nguoi dung - Dao tao

```mermaid
classDiagram
direction TB

class NguoiDung {
  +ma_nguoi_dung: int
  +ho_ten: string
  +email: string
  +vai_tro: admin|giang_vien|hoc_vien
  +trang_thai: bool
}

class HocVien {
  +nguoi_dung_id: int
  +ma_hoc_vien: string
  +lop: string
  +nganh: string
}

class GiangVien {
  +nguoi_dung_id: int
  +chuyen_nganh: string
  +hoc_vi: string
  +so_gio_day: decimal
}

class KhoaHoc {
  +ma_khoa_hoc: string
  +ten_khoa_hoc: string
  +loai: mau|hoat_dong
  +trang_thai_van_hanh: string
}

class ModuleHoc {
  +ma_module: string
  +ten_module: string
  +thu_tu_module: int
  +so_buoi: int
}

class LopHoc {
  +ma_lop_hoc: string
  +ngay_khai_giang: date
  +ngay_ket_thuc: date
}

class LichHoc {
  +ngay_hoc: date
  +gio_bat_dau: time
  +gio_ket_thuc: time
  +hinh_thuc: online|truc_tiep
  +trang_thai: cho|dang_hoc|hoan_thanh|huy
}

class HocVienKhoaHoc {
  +ngay_tham_gia: date
  +trang_thai: dang_hoc|hoan_thanh|ngung_hoc
}

class PhanCongModuleGiangVien {
  +ngay_phan_cong: datetime
  +trang_thai: cho_xac_nhan|da_nhan|tu_choi
}

NguoiDung "1" --> "0..1" HocVien : has_one
NguoiDung "1" --> "0..1" GiangVien : has_one
KhoaHoc "1" --> "*" LopHoc : has_many
KhoaHoc "1" --> "*" ModuleHoc : has_many
KhoaHoc "1" --> "*" LichHoc : has_many
LopHoc "1" --> "*" LichHoc : has_many
ModuleHoc "1" --> "*" LichHoc : has_many
HocVien "1" --> "*" HocVienKhoaHoc : has_many
KhoaHoc "1" --> "*" HocVienKhoaHoc : has_many
GiangVien "1" --> "*" PhanCongModuleGiangVien : has_many
KhoaHoc "1" --> "*" PhanCongModuleGiangVien : has_many
ModuleHoc "1" --> "*" PhanCongModuleGiangVien : has_many
LichHoc "*" --> "0..1" GiangVien : belongs_to
```

## 3. Noi dung - Live room

```mermaid
classDiagram
direction TB

class LichHoc {
  +id: int
  +ngay_hoc: date
  +hinh_thuc: string
  +link_online: string
}

class BaiGiang {
  +id: int
  +khoa_hoc_id: int
  +module_hoc_id: int
  +lich_hoc_id: int
  +tieu_de: string
  +loai_bai_giang: string
  +trang_thai_duyet: string
  +trang_thai_cong_bo: string
}

class TaiNguyenBuoiHoc {
  +id: int
  +lich_hoc_id: int
  +loai_tai_nguyen: string
  +tieu_de: string
  +trang_thai_duyet: string
  +trang_thai_xu_ly: string
}

class PhongHocLive {
  +id: int
  +bai_giang_id: int
  +giang_vien_id: int
  +platform_type: internal|zoom|google_meet
  +external_meeting_url: string
  +trang_thai: string
  +trang_thai_duyet: string
  +trang_thai_cong_bo: string
}

class PhongHocLiveNguoiThamGia {
  +id: int
  +phong_hoc_live_id: int
  +nguoi_dung_id: int
}

class PhongHocLiveBanGhi {
  +id: int
  +phong_hoc_live_id: int
}

class LiveRoomLinkHistory {
  +id: int
  +phong_hoc_live_id: int
  +lich_hoc_id: int
  +provider: string
  +updated_by: int
}

class GiangVien
class NguoiDung

LichHoc "1" --> "*" TaiNguyenBuoiHoc : has_many
LichHoc "1" --> "*" BaiGiang : has_many
BaiGiang "*" --> "0..1" TaiNguyenBuoiHoc : tai_nguyen_chinh
BaiGiang "*" --> "*" TaiNguyenBuoiHoc : tai_nguyen_phu_pivot
BaiGiang "1" --> "0..1" PhongHocLive : has_one
PhongHocLive "*" --> "1" GiangVien : belongs_to
PhongHocLive "*" --> "0..1" NguoiDung : moderator
PhongHocLive "*" --> "0..1" NguoiDung : tro_giang
PhongHocLive "1" --> "*" PhongHocLiveNguoiThamGia : has_many
PhongHocLive "1" --> "*" PhongHocLiveBanGhi : has_many
PhongHocLive "1" --> "*" LiveRoomLinkHistory : has_many
PhongHocLiveNguoiThamGia "*" --> "1" NguoiDung : belongs_to
LiveRoomLinkHistory "*" --> "1" LichHoc : belongs_to
LiveRoomLinkHistory "*" --> "1" NguoiDung : nguoi_cap_nhat
```

## 4. Kiem tra - Ket qua

```mermaid
classDiagram
direction LR

class BaiKiemTra {
  +id: int
  +khoa_hoc_id: int
  +module_hoc_id: int
  +lich_hoc_id: int
  +tieu_de: string
  +loai_noi_dung: string
  +che_do_noi_dung: string
  +co_giam_sat: bool
  +trang_thai_phat_hanh: string
}

class NganHangCauHoi {
  +id: int
  +khoa_hoc_id: int
  +module_hoc_id: int
  +ma_cau_hoi: string
  +loai_cau_hoi: string
  +kieu_dap_an: string
  +trang_thai: string
}

class DapAnCauHoi {
  +id: int
  +ngan_hang_cau_hoi_id: int
  +ky_hieu: string
  +is_dap_an_dung: bool
}

class ChiTietBaiKiemTra {
  +id: int
  +bai_kiem_tra_id: int
  +ngan_hang_cau_hoi_id: int
  +thu_tu: int
  +diem_so: decimal
}

class BaiLamBaiKiemTra {
  +id: int
  +bai_kiem_tra_id: int
  +hoc_vien_id: int
  +lan_lam_thu: int
  +trang_thai: string
  +diem_so: decimal
  +trang_thai_giam_sat: string
}

class ChiTietBaiLamBaiKiemTra {
  +id: int
  +bai_lam_bai_kiem_tra_id: int
  +chi_tiet_bai_kiem_tra_id: int
  +ngan_hang_cau_hoi_id: int
  +dap_an_cau_hoi_id: int
  +diem_tu_dong: decimal
  +diem_tu_luan: decimal
}

class KetQuaHocTap {
  +id: int
  +khoa_hoc_id: int
  +hoc_vien_id: int
  +module_hoc_id: int
  +bai_kiem_tra_id: int
  +source_attempt_id: int
  +diem_tong_ket: decimal
  +diem_giang_vien_chot: decimal
  +trang_thai_chot: string
  +trang_thai_duyet: string
}

class PhieuXetDuyetKetQua {
  +id: int
  +khoa_hoc_id: int
  +phan_cong_id: int
  +giang_vien_id: int
  +nguoi_lap_id: int
  +phuong_an: string
  +trang_thai: string
}

class ChiTietPhieuXetDuyetKetQua {
  +id: int
  +phieu_id: int
  +hoc_vien_id: int
}

class KhoaHoc
class ModuleHoc
class LichHoc
class NguoiDung
class GiangVien
class PhanCongModuleGiangVien

KhoaHoc "1" --> "*" BaiKiemTra : has_many
ModuleHoc "1" --> "*" BaiKiemTra : has_many
LichHoc "1" --> "*" BaiKiemTra : has_many
BaiKiemTra "1" --> "*" ChiTietBaiKiemTra : has_many
NganHangCauHoi "1" --> "*" DapAnCauHoi : has_many
ChiTietBaiKiemTra "*" --> "1" NganHangCauHoi : belongs_to
BaiKiemTra "1" --> "*" BaiLamBaiKiemTra : has_many
BaiLamBaiKiemTra "1" --> "*" ChiTietBaiLamBaiKiemTra : has_many
ChiTietBaiLamBaiKiemTra "*" --> "1" ChiTietBaiKiemTra : belongs_to
ChiTietBaiLamBaiKiemTra "*" --> "1" NganHangCauHoi : belongs_to
ChiTietBaiLamBaiKiemTra "*" --> "0..1" DapAnCauHoi : belongs_to
BaiLamBaiKiemTra "*" --> "1" NguoiDung : hoc_vien
KetQuaHocTap "*" --> "1" KhoaHoc : belongs_to
KetQuaHocTap "*" --> "0..1" ModuleHoc : belongs_to
KetQuaHocTap "*" --> "1" NguoiDung : hoc_vien
KetQuaHocTap "*" --> "0..1" BaiKiemTra : belongs_to
KetQuaHocTap "*" --> "0..1" BaiLamBaiKiemTra : source_attempt
PhieuXetDuyetKetQua "*" --> "1" KhoaHoc : belongs_to
PhieuXetDuyetKetQua "*" --> "1" PhanCongModuleGiangVien : belongs_to
PhieuXetDuyetKetQua "*" --> "1" GiangVien : belongs_to
PhieuXetDuyetKetQua "1" --> "*" ChiTietPhieuXetDuyetKetQua : has_many
ChiTietPhieuXetDuyetKetQua "*" --> "1" NguoiDung : hoc_vien
```

## 5. Cach ve dung cho project nay

1. Bat dau tu `app/Models`, khong bat dau tu controller.
2. Moi class chi nen giu 3-6 thuoc tinh quan trong nhat.
3. Quan he pivot nen ve thanh class rieng neu pivot co nghiep vu:
   `HocVienKhoaHoc`, `PhanCongModuleGiangVien`, `ChiTietBaiKiemTra`.
4. Service nhu `TeacherScheduleLiveRoomService`, `CourseResultAggregationService`, `LiveRoomPlatformService`
   nen dua vao sequence/activity diagram, khong nen day vao class diagram domain.
5. Neu ban muon lam bao cao/luan van:
   dung 1 so do overview va 2-3 so do chi tiet theo tung phan he.

## 6. Phan bo sung nen them sau

De so do day du hon, ban co the ve them 1 nhanh "Ho tro he thong":
- `ThongBao`
- `YeuCauHocVien`
- `DiemDanh`
- `DiemDanhGiangVien`
- `GiangVienDonXinNghi`
- `SystemSetting`
- `Banner`

Nhung minh khuyen nghi de nhom nay o mot so do rieng, tranh lam roi overview chinh.
