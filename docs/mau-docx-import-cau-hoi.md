# Mau DOCX import cau hoi

File Word de parser nhan dien tot nhat nen theo dung cau truc sau:

## Quy tac

- Moi cau hoi bat dau bang so thu tu: `1.` , `2.` , `3.`
- Moi dap an bat dau bang `A.` , `B.` , `C.` , `D.` hoac `A)` , `B)` , `C)` , `D)`
- Mot cau hoi nen co dung 4 dap an neu muon import on dinh nhat
- Dap an dung nen duoc danh dau bang mot trong ba cach:
  - boi vang
  - in dam
  - them ky hieu `*` ngay dau noi dung dap an
- Khong chen dong trung gian nhu `1/1`, `Trang 1/10`, header/footer vao giua cau hoi va dap an
- Khong dung bang, anh chup, textbox dac biet de chua noi dung cau hoi
- Neu trong file co cau dien tu, viet tat, tu luan hoac thieu dap an thi he thong van co the preview, nhung se danh dau loi va khong import thang

## Mau de copy

```text
1. Quan ly du an la gi?
A. Mot cong viec lap di lap lai trong thoi gian dai
B. Mot qua trinh gom nhieu cong viec co lien quan, duoc thuc hien de dat muc tieu cu the
C. Mot cong viec khong co gioi han ve thoi gian va chi phi
D. Mot hoat dong van hanh thuong xuyen

2. WBS la gi?
A. Bang phan bo nhan su
B. Ke hoach truyen thong
C. Cau truc phan ra cong viec cua du an
D. Bao cao tong ket du an
```

## Luu y dap an dung

- Neu dung `*`, hay dat nhu sau:

```text
1. Framework nao dang duoc dung trong project nay?
A. Django
B. *Laravel
C. Rails
D. Spring
```

- Neu dung Word, cach an toan nhat la boi vang hoac in dam dap an dung.

## File mau da tao san

Ban co the dung ngay file nay:

- `storage/app/templates/imports/cau-hoi/mau-import-cau-hoi-tu-word.docx`
