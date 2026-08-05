# 📚 Library Management System

A complete Library Management System built with **Core PHP, MySQL, HTML5, CSS3, and JavaScript** (no frameworks) — with **two portals**: a Librarian/Admin panel and a self-service Student portal.

## How It Works

- **Students** register themselves (`register.php`) → their account sits as **Pending** → a librarian approves or rejects it (`members/pending_members.php`) → once approved, the student can log in (`student_login.php`), browse the catalog, and **request** to borrow a book.
- **Librarians/Admins** log in separately (`login.php`) and manage the whole system: books, members, approving registrations, approving/rejecting borrow requests, issuing/returning books directly, and viewing reports.
- A librarian can also add a member directly (`members/add_member.php`) — that account is Active immediately and doesn't need approval.

This mirrors the common approval-workflow pattern used by real library systems (student self-registration → librarian approval → self-service borrowing requests).

## Features

**Librarian/Admin side:**
- Secure login & logout (PHP Sessions, hashed passwords)
- Dashboard with live stats + pending-approval alerts
- Book Management: Add, Edit, Delete, View (with search)
- Member Management: Add, Edit, Delete, View (with search)
- **Pending Registrations** — approve/reject new student sign-ups
- **Book Requests** — approve/reject student borrow requests (approving auto-issues the book)
- Issue Book & Return Book (direct, front-desk style) with automatic late fines
- Search Books, Reports (category breakdown, most borrowed, overdue, fines collected)

**Student side:**
- Self-registration with account-approval workflow
- Student login (separate from librarian login)
- Personal dashboard (borrowed books, overdue count, pending requests, fines)
- Browse/search the catalog and **request to borrow** a book
- View borrowed books & full borrowing history
- Track the status of submitted requests
- Edit profile & change password

**Shared:**
- Client-side (JavaScript) + server-side (PHP) form validation
- Fully responsive UI with reusable header/sidebar/footer components
- All queries use prepared statements (SQL-injection safe)

## Project Structure

```
library-management-system/
├── config/
│   └── config.php                # Database connection + session start
├── includes/
│   ├── header.php                 # Top navbar (role-aware: admin or student)
│   ├── sidebar.php                # Librarian/admin navigation
│   ├── student_sidebar.php        # Student portal navigation
│   ├── footer.php                 # Footer + closing tags
│   ├── auth_check.php             # Session guard for librarian/admin pages
│   └── student_auth_check.php     # Session guard for student pages
├── assets/
│   ├── css/style.css              # All styles (responsive, modern UI)
│   └── js/
│       ├── script.js              # Sidebar toggle, delete confirm, alerts
│       └── validation.js          # Client-side form validation (all forms)
├── database/
│   └── library_management.sql    # Full schema + sample dummy data
├── books/                         # Librarian: book CRUD
│   ├── view_books.php / add_book.php / edit_book.php / delete_book.php
├── members/                       # Librarian: member CRUD + approvals
│   ├── view_members.php / add_member.php / edit_member.php / delete_member.php
│   └── pending_members.php        # Approve/reject student registrations
├── requests/
│   └── manage_requests.php        # Librarian: approve/reject borrow requests
├── student/                       # Student self-service portal
│   ├── student_dashboard.php
│   ├── browse_books.php           # Search + request to borrow
│   ├── my_books.php                # Current + past borrowed books
│   ├── my_requests.php             # Request status tracker
│   └── profile.php                 # Edit info / change password
├── index.php                      # Public landing page
├── login.php / logout.php         # Librarian/Admin auth
├── register.php                   # Student self-registration
├── student_login.php / student_logout.php   # Student auth
├── dashboard.php                  # Librarian dashboard
├── issue_book.php                 # Direct issue (front-desk)
├── return_book.php                # Return + fine calculation
├── search_books.php               # Librarian book search
└── reports.php                    # Analytics/reports
```

## Database Tables

| Table          | Purpose                                                          |
|----------------|-------------------------------------------------------------------|
| `admins`       | Librarian/Admin login accounts                                    |
| `books`        | Book catalog (title, author, ISBN, copies, etc.)                  |
| `members`      | Student accounts — login credentials + approval `status`          |
| `issued_books` | Issue/return transactions, due dates, fines                       |
| `book_requests`| Student borrow requests awaiting librarian approval/rejection     |

`members.status` is one of: `Pending` (awaiting approval) → `Active` (can log in and borrow) / `Rejected` / `Inactive`.

## How to Run in XAMPP

1. **Install XAMPP** (if not already installed): https://www.apachefriends.org/

2. **Copy the project folder** into your XAMPP `htdocs` directory, e.g.:
   - Windows: `C:\xampp\htdocs\library-management-system`
   - macOS: `/Applications/XAMPP/htdocs/library-management-system`
   - Linux: `/opt/lampp/htdocs/library-management-system`

3. **Start Apache and MySQL** from the XAMPP Control Panel.

4. **Create the database**
   - Go to `http://localhost/phpmyadmin`
   - Click **Import** → choose `database/library_management.sql` → **Go**
   - This creates the `library_management` database, all 5 tables, and sample data.

5. **Check `config/config.php`** (defaults already match a fresh XAMPP install):
   ```php
   DB_HOST = localhost
   DB_USER = root
   DB_PASS = ''  (empty)
   DB_NAME = library_management
   ```

6. **Open the project**
   ```
   http://localhost/library-management-system/
   ```

## Demo Logins

| Role       | Username / Email             | Password     |
|------------|-------------------------------|--------------|
| Admin      | `admin`                       | `admin123`   |
| Librarian  | `librarian`                   | `admin123`   |
| Student    | `john.carter@example.com`     | `student123` |

A 5th demo member (`priya.nair@example.com`) is seeded with status **Pending** — log in as admin and visit **Pending Registrations** to see the approval flow in action.

## Try the Full Workflow

1. Go to `register.php` and sign up as a new student → account is created as **Pending**.
2. Log in as `admin` (`login.php`) → **Pending Registrations** → Approve the new account.
3. Log in as that student (`student_login.php`) → **Browse Books** → click **Request to Borrow** on any available title.
4. Log back in as `admin` → **Book Requests** → **Approve** → the book is now issued and available copies drop by one.
5. As the student, check **My Requests** (status: Approved) and **My Borrowed Books** (now listed).
6. As admin, go to **Return Book** to process the return and see any late fine calculated automatically.

## Notes

- Passwords are stored using PHP's `password_hash()` / verified with `password_verify()`.
- Sessions are separate for the two portals (`admin_id` vs `student_id`), each protected by its own auth guard.
- The late-return fine is **৳5/day**, changeable in `return_book.php` (`FINE_PER_DAY` constant).
- Deleting a book or member is blocked while they have active (unreturned) issue records.
- A student can't submit a duplicate request for a book they already have out or already have pending.

## Tech Stack

- **Frontend:** HTML5, CSS3, Vanilla JavaScript
- **Backend:** Core PHP (no framework), MySQLi with prepared statements
- **Database:** MySQL
