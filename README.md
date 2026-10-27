# Unsent 💌
### A Private Space for Your Unsent Thoughts

**Unsent** is a digital journaling application designed to provide a safe, private, and comfortable space for people to express their thoughts, emotions, and words they may not be ready to share with others.

Sometimes, there are things we want to say but cannot bring ourselves to express. Whether it's a letter to someone special, a message to our past selves, or feelings that are difficult to put into words, Unsent allows users to write freely without the pressure of sending or receiving a response.

Rather than functioning as a traditional messaging application, Unsent focuses on personal expression, emotional reflection, and the freedom to write without expectations.

## ✨ Features

- **Private Journaling** — Write down personal thoughts, feelings, and messages in a comfortable space.
- **Unsent Letters** — Create letters addressed to yourself, someone else, your past, or anyone meaningful to you.
- **Personal Expression** — Express emotions freely without the pressure of direct communication.
- **Organized Entries** — Keep written thoughts and letters organized for future reflection.
- **Clean User Interface** — Enjoy a simple, calm, and user-friendly design.
- **Personalization** — Customize the writing experience to make it feel more personal.

## 🎯 Project Objectives

Unsent aims to make emotional expression more accessible by providing a digital space where users can process their thoughts and feelings through writing.

The project focuses on:
- Encouraging self-reflection and emotional expression.
- Providing an alternative to sending messages directly.
- Creating a comfortable and intuitive journaling experience.
- Applying Human-Computer Interaction (HCI) principles to improve usability and accessibility.

## 🎨 Design & User Experience

The interface is designed with simplicity and comfort in mind, helping users focus on their writing rather than unnecessary distractions.

The design process considers usability principles, including consistency, clear feedback, error prevention, user control, and ease of navigation. These principles help create a more intuitive and comfortable experience.

The application is developed as part of a web application development project, with its interface and user flows designed and prototyped in Figma.

## 🛠️ Technologies Used

- **Frontend:** HTML, CSS, JavaScript
- **Backend:** PHP
- **Local Development Environment:** Laragon & Devilbox
- **Database:** MySQL (if applicable)
- **UI/UX Design & Prototyping:** Figma
- **Version Control:** Git and GitHub

## 🔮 Future Improvements

Potential improvements for Unsent include:
- Enhanced letter organization and search.
- Additional personalization options.
- Improved writing and journaling interactions.
- Responsive layouts for different screen sizes.
- Additional privacy and security features.

## 👥 Project Information

- **Project Name:** Unsent
- **Project Type:** Digital Journaling Application
- **Field:** Human-Computer Interaction (HCI) / UI/UX Design
- **Design Tool:** Figma
- **Repository:** [miraclemikhael/unsent](https://github.com/miraclemikhael/unsent)

## 📝 License

This project is developed for educational purposes. Add a license if you intend to distribute or reuse the project publicly.

---

*Some words are easier to write than to say. Unsent gives them a place to exist.* 💙

## 🗂️ File map (for beginners)

- `index.php` — front door. Reads `?p=page` and loads the right page.
- `login.php`, `register.php`, `forgot-password.php`, `logout.php` — sign in / out pages.
- `process_*.php` — scripts that receive form posts (save data, then redirect).
- `user/` — pages for normal users: home, write, unsent letters, profile.
- `admin/` — pages for admins: overview, users, activity, audit log, settings.
- `includes/` — shared helpers: `functions.php` (small tools), `header.php`, `footer.php`, `sidebar.php`, `icons.php`.
- `config/` — settings (`config.php`) and the MySQL connection + load/save (`database.php`).
- `css/style.css` — main look. `css/motion.css` — fades and flips. `css/responsive.css` — phone layout.
- `js/` — browser scripts, one per page feature.

### Helper names

Short helper names were renamed to readable ones, e.g. `e()` → `esc()`, `ic()` → `icon()`, `db()` → `load_data()`,
`sv()` → `save_data()`, `go()` → `redirect_to_page()`, `isadmin()` → `is_admin()`.

## 🗄️ Database (MySQL / HeidiSQL)

All data now lives in MySQL, database `unsent` (tables: `users`, `moods`, `letters`, `audit_log`, `settings`).

1. Start Laragon (Apache + MySQL).
2. In HeidiSQL: `File` → `Run SQL file...` → pick `unsent_database.sql` (kept outside Git, it holds password hashes).
3. Open `http://unsent.test/db_test.php`. It should say "SAME. All good." Then delete `db_test.php`.

MySQL login is set at the top of `config/database.php` (Laragon default: root, no password).

Every table has `created_at` and `updated_at` columns (MySQL fills them in by itself). `updated_at` only changes when a row really changes.

### Times recorded
- Every table: `created_at`, `updated_at`.
- `users`: `last_login_at`, `last_logout_at`, `password_changed_at`.
- `letters`: `written_at` (exact time). `moods`: `logged_at` (exact time).
- `deleted_log`: what was deleted (users, letters) and when.
Upgrade an old database by running `upgrade_timestamps.sql` once in HeidiSQL.
