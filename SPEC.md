# Đặc tả yêu cầu: Hệ thống gửi DM (DM送信システム)

> Phiên bản: 2.1 — bản rút gọn, dùng Livewire Starter Kit
> Nguyên tắc: chỉ làm lại các chức năng đã có trong file Excel `DM送信.xls` ("VYメール"), cải tiến để dễ dùng và ổn định hơn. Không thêm chức năng mới, trừ phần hủy đăng ký tối thiểu (xem mục 1.3).

## 1. Tổng quan

### 1.1 Bối cảnh
Công ty đang gửi DM bằng file Excel chạy macro VBA. Các vấn đề cần giải quyết:

- Excel bị treo trong lúc gửi, phải để máy mở suốt.
- Mật khẩu SMTP nằm lộ trong file.
- Phải gõ biến `#$1$#`, `#$2$#`… bằng tay, soạn nội dung HTML khó.
- Đính kèm phải nhập đường dẫn file trên máy cá nhân.
- Macro `.xls` dễ bị Windows/antivirus chặn, công nghệ `CDO` đã cũ.

### 1.2 Mục tiêu
Web app nội bộ bằng Laravel, người không rành công nghệ dùng được, giữ đúng quy trình quen thuộc của file Excel: **cài đặt → danh sách người nhận → mẫu → gửi thử → gửi hàng loạt**.

### 1.3 Ngoại lệ được giữ lại: hủy đăng ký tối thiểu
File Excel không có, nhưng được giữ vì:
- 特定電子メール法 bắt buộc mail quảng cáo phải có cách hủy nhận.
- Gmail/Yahoo yêu cầu header hủy đăng ký với người gửi số lượng lớn, thiếu thì dễ vào spam.

Chỉ làm ở mức tối thiểu: link + header, người hủy được tự động loại khỏi lần gửi sau. Không làm màn hình quản lý riêng.

### 1.4 Ngoài phạm vi
Phân quyền nhiều cấp, quản lý sự đồng ý nhận mail, thống kê mở mail, dashboard, export, nhiều tài khoản gửi (sheet 追加送信元登録 đang trống), chuyển sang Amazon SES (để sau).

## 2. Công nghệ

| Hạng mục | Lựa chọn |
|---|---|
| Framework | Laravel 13, PHP 8.3+ |
| Database | MySQL 8 |
| Queue | Driver `database` |
| Starter kit | Laravel Livewire Starter Kit (Livewire 4 + Flux UI bản miễn phí + Tailwind), tắt chức năng đăng ký |
| Giao diện | Livewire component + Blade; trình soạn thảo bọc trong `wire:ignore` |
| Import | `maatwebsite/excel` |
| Trình soạn thảo | TinyMCE bản self-hosted |
| Mail dev | Mailpit hoặc `MAIL_MAILER=log` |
| Ngôn ngữ / múi giờ | Tiếng Nhật / `Asia/Tokyo` |

## 3. Người dùng
- Nhân viên công ty đăng nhập bằng email + mật khẩu. Tất cả có quyền như nhau.
- Không có trang đăng ký; tài khoản tạo bằng lệnh `php artisan` hoặc seeder.

## 4. Cấu hình

### 4.1 SMTP — file `.env` (thay phần SMTP của sheet 設定and操作)
```env
MAIL_MAILER=smtp
MAIL_HOST=
MAIL_PORT=587
MAIL_USERNAME=
MAIL_PASSWORD=
MAIL_FROM_ADDRESS=
MAIL_FROM_NAME=
```
Không commit `.env`. Cổng 587 dùng STARTTLS; không tắt kiểm tra chứng chỉ.

### 4.2 Màn hình 設定 (bảng `settings`)
| Mục | Tương ứng Excel | Ghi chú |
|---|---|---|
| 保存用BCCアドレス | Bccアドレス（保存用） | tùy chọn |
| 送信間隔（秒） | 送信間隔 | mặc định 3 |
| 署名（フッター） | phần chữ ký cuối mail | bắt buộc nhập: tên công ty, địa chỉ, liên hệ |

## 5. Mô hình dữ liệu

### `recipients` — 送信リスト
| Cột | Kiểu | Ghi chú |
|---|---|---|
| id | bigint | hiển thị là「No」giống Excel |
| email | string | 宛先アドレス (To) |
| cc, bcc | text, nullable | nhiều địa chỉ phân tách bằng `;` |
| company_name | string, nullable | 宛先会社（団体）名 |
| person_name | string, nullable | 宛先名前 |
| honorific | string, default `様` | 敬称 |
| custom_fields | json, nullable | thay cho `#$1$#`…`#$30$#`, tên mục lấy từ tiêu đề cột khi import |
| exclude | boolean, default false | 送信しない |
| unsubscribed_at | datetime, nullable | ngày hủy đăng ký |
| last_sent_at | datetime, nullable | 送信済 |
| timestamps | | |

### `templates` — 件名定型文・本文定型文
`id`, `name`, `subject`, `body_html`, `body_text`, `timestamps`.

### `send_jobs` — một lần bấm gửi
`id`, `template_id`, `status` (`sending`, `paused`, `completed`, `cancelled`), `batch_id`, `total`, `sent`, `failed`, `created_by`, `timestamps`.

### `send_job_items`
`id`, `send_job_id`, `recipient_id`, `status` (`pending`, `sent`, `failed`), `error_message`, `sent_at`.

### `send_job_attachments`
`id`, `send_job_id`, `path`, `original_name`, `size`.

### `settings`
`key`, `value`.

## 6. Chức năng

### F-01 送信リスト (danh sách người nhận)
- Hiển thị dạng bảng giống sheet Excel: cột No, メールアドレス, 会社名, 名前, 敬称, các 追加項目, 送信しない, 最終送信日.
- Thêm, sửa, xóa từng dòng; tìm kiếm bằng một ô duy nhất.
- Tick「送信しない」trực tiếp trên bảng.
- Dòng đã hủy đăng ký hiển thị nhãn xám「配信停止」và không tick gửi được.

### F-02 Import từ Excel
- Nút「Excelから取り込む」: nhận `.xlsx` và `.csv`, tự nhận diện UTF-8 / Shift_JIS.
- Nút「ひな形をダウンロード」: tải file mẫu có sẵn tiêu đề cột tiếng Nhật.
- Đọc được trực tiếp sheet `送信リスト` của file Excel cũ (sau khi lưu thành `.xlsx`).
- Cột không thuộc các trường cố định → lưu vào `custom_fields` với tên = tiêu đề cột.
- Xem trước 10 dòng đầu, rồi chọn「追加する」hoặc「すべて置き換える」.
- Báo lỗi theo dòng:「15行目：メールアドレスの形式が正しくありません」.

### F-03 テンプレート (mẫu tiêu đề và nội dung)
- Soạn nội dung bằng trình soạn thảo trực quan giống Word, không cần biết HTML.
- Nút「差し込み項目を挿入」chèn biến: 会社名, 名前, 敬称 và các 追加項目 hiện có. Biến hiển thị dạng nhãn, ví dụ [会社名]; lưu trong DB dạng `{{company}}`, `{{name}}`, `{{honorific}}`, `{{custom.<tên>}}`.
- Tiêu đề cũng chèn biến được.
- Bản text tự sinh khi lưu.
- Xem trước với dữ liệu của một người nhận chọn từ danh sách.
- Mục「HTMLで編集」cho người cần dán HTML cũ từ Excel.

### F-04 送信 (màn hình gửi, một trang duy nhất)
Từ trên xuống dưới:
1. Chọn テンプレート (menu thả xuống, bên cạnh có khung xem trước).
2. Chọn người nhận: 「すべて」hoặc「No ○ 〜 No ○」(tương ứng 送信開始No・送信終了No), hoặc tick chọn trong bảng. Tự loại các dòng 送信しない và 配信停止. Hiển thị:「送信予定：120件（対象外：5件）」.
3. 添付ファイル: kéo thả tối đa 3 file, tổng ≤ 10MB (thay cho đường dẫn file trong Excel).
4. Nút「テスト送信」: gửi tới email người đang đăng nhập (sửa được). **Phải gửi thử ít nhất một lần thì nút gửi thật mới bật.**
5. Nút「一括送信」: hộp thoại xác nhận ghi rõ số người nhận, tick「内容を確認しました」mới bấm được.

### F-05 Gửi hàng loạt và theo dõi
- Mỗi người nhận là một Job trong `Bus::batch()`, giãn cách theo 送信間隔 bằng middleware `RateLimited`.
- Lỗi → retry 3 lần, vẫn lỗi → `failed`.
- Màn hình tiến độ: thanh tiến độ,「送信済 80 / 120件」, thời gian còn lại, dòng chữ「このページを閉じても送信は続きます」.
- Nút「一時停止」「再開」「中止」(tương ứng 一時停止／中断 của Excel).
- Nút「失敗分を再送信」khi có mục lỗi.
- Gửi xong: cập nhật `last_sent_at` của người nhận (tương ứng 送信済).
- Trang danh sách các lần gửi gần đây (ngày, mẫu, số đã gửi / lỗi) để xem lại.

### F-06 Nội dung mail gửi đi
- Multipart HTML + text, UTF-8.
- Chân mail lấy từ 署名 trong cài đặt, kèm link「配信停止はこちら」.
- Header `List-Unsubscribe` và `List-Unsubscribe-Post: List-Unsubscribe=One-Click`.
- CC/BCC theo từng người nhận; BCC lưu trữ nếu có cài đặt.

### F-07 Hủy đăng ký (tối thiểu)
- Route `/unsubscribe/{recipient}` dùng `URL::signedRoute` + middleware `signed`, không cần đăng nhập, loại khỏi CSRF.
- GET: trang xác nhận tiếng Nhật với nút「配信を停止する」. POST: ghi `unsubscribed_at`, hiển thị trang hoàn tất.
- Muốn gửi lại cho người đã hủy: nhân viên bỏ nhãn trong màn hình sửa người nhận (có hộp thoại cảnh báo).

### F-08 使い方
- Một trang hướng dẫn ngắn có ảnh chụp màn hình, thay cho sheet 利用方法 của Excel.

## 7. Yêu cầu giao diện
- Toàn bộ tiếng Nhật, dùng đúng từ ngữ của file Excel cũ (送信リスト, テンプレート, テスト送信, 一括送信, 送信しない, 送信済) để người dùng thấy quen.
- Menu chỉ có 4 mục: 送信リスト / テンプレート / 送信 / 設定.
- Mỗi màn hình một nút chính nổi bật; chữ tối thiểu 14px; ưu tiên màn hình PC.
- Thông báo lỗi tiếng Nhật dễ hiểu, nói rõ cần làm gì; không hiện mã lỗi hay thông báo SMTP gốc.
  - Ví dụ:「メールサーバーに接続できませんでした。管理者に連絡してください。」
- Xóa dữ liệu luôn có hộp thoại xác nhận.

## 8. Yêu cầu phi chức năng
- `.env` không commit; file đính kèm lưu thư mục private.
- Queue worker chạy bằng Supervisor; đóng trình duyệt thì việc gửi vẫn tiếp tục.
- Môi trường dev không dùng SMTP thật.
- Gửi được 3.000 người nhận trong một lần mà không lỗi.

## 9. Tiêu chí nghiệm thu
- [ ] Import sheet `送信リスト` từ file Excel cũ, dữ liệu hiển thị đúng.
- [ ] Biến trong tiêu đề và nội dung thay đúng cho từng người.
- [ ] Gửi thử tới Gmail: tiếng Nhật hiển thị đúng, có bản text, Gmail hiện nút hủy đăng ký.
- [ ] Bấm hủy đăng ký → lần gửi sau tự loại người đó.
- [ ] Gửi 200 người: đúng giãn cách, tạm dừng / tiếp tục / hủy hoạt động đúng; đóng trình duyệt vẫn gửi tiếp.
- [ ] Không gửi thật được nếu chưa gửi thử và chưa tick xác nhận.
- [ ] Một nhân viên từng dùng file Excel tự làm được từ import đến gửi thử mà không cần hỏi.

## 10. Đối chiếu với file Excel cũ

| Excel cũ | App mới | Cải tiến |
|---|---|---|
| 設定and操作 (SMTP) | `.env` | mật khẩu không còn lộ |
| Bcc保存用, 送信間隔 | Màn hình 設定 | |
| 送信リスト | F-01, F-02 | bảng tìm kiếm được, import từ Excel |
| 送信開始No・終了No | F-04 chọn khoảng No | |
| 送信しない / 送信済 | `exclude` / `last_sent_at` | |
| 件名定型文・本文定型文 | F-03 | soạn như Word, chèn biến bằng nút |
| `#$1$#`〜`#$30$#` | 追加項目 | đặt tên dễ hiểu theo tiêu đề cột |
| 添付ファイル (đường dẫn) | F-04 kéo thả | |
| テスト送信 | F-04 | bắt buộc trước khi gửi thật |
| 一括送信, 一時停止／中断 | F-05 | không treo máy, có tiến độ, gửi lại mục lỗi |
| 利用方法 | F-08 | |
| (không có) | F-07 hủy đăng ký | yêu cầu pháp luật, chống spam |