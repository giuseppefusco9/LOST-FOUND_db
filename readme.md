# 🧭 LOST & FOUND – Database Web Application

A web application for managing **lost and found items**, developed in PHP with a MySQL database.
The system allows users to report, search, and manage lost or found items, while administrators can supervise and moderate submissions.

---

## 🚀 Main Features

* 👤 **User Authentication** (Admin and User)
* 📦 **Item Reporting** for lost or found objects
* 🔍 **Dynamic Search** by category, description, or date
* 🗂️ **Database Management** via the provided SQL file
* 📋 **Admin Dashboard** for user and report management

---

## 🧩 Technologies Used

* **Frontend:** HTML5, CSS3, JavaScript
* **Backend:** PHP 7+
* **Database:** MySQL
* **Local Environment:** XAMPP

---

## ⚙️ Installation & Configuration

### 1️⃣ Clone or Download the Project

Download the repository or clone it into your XAMPP directory:

```bash
C:\xampp\htdocs\LOST-FOUND_db
```

Or using Git:

```bash
git clone https://github.com/giuseppefusco9/LOST-FOUND_db.git
```

### 2️⃣ Start XAMPP Services

Open the **XAMPP Control Panel** and start:

* **Apache**
* **MySQL**

### 3️⃣ Import the Database

1. Open **MySQL Workbench** or **phpMyAdmin**
2. Create a new database (e.g., `lost_found`)
3. Import the SQL file included in the project directory:

   ```
   LOST&FOUND.sql
   ```

### 4️⃣ Run the Application

Open your browser and go to:

```
http://localhost/LOST-FOUND_db
```

### 5️⃣ Follow the Instructions

Refer to the provided documentation or report for details on navigation and functionality.

---

## 🔐 Access Credentials

### 👑 **Admin**

* **Email:** [admin1@email.com](mailto:admin1@email.com)
* **Password:** adminpass123

### 👤 **Test Users**

* **Email:** [giorgio.verdi@email.com](mailto:giorgio.verdi@email.com)
  **Password:** password1234
* **Email:** [mario.rossi@email.com](mailto:mario.rossi@email.com)
  **Password:** password123

---

## 🧱 Project Structure

```
LOST-FOUND_db/
│
├── assets/              # CSS, JS, and image files
├── includes/            # Reusable PHP files (DB connection, header, footer, etc.)
├── pages/               # Main application pages
├── LOST&FOUND.sql       # SQL file for database creation
├── index.php            # Homepage / login page
└── README.md            # Project documentation
```

---

## 🧪 Testing

To perform a full test:

1. Follow the installation steps above
2. Log in using the provided credentials
3. Try the features for reporting, searching, and managing lost/found items

---

## 👨‍💻 Author

**Giuseppe Fusco**
📧 [GitHub – giuseppefusco9](https://github.com/giuseppefusco9)

---

## 📝 License

This project is distributed for educational purposes.
You are free to modify and reuse it by citing the original source.
