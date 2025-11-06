# 🌐 BlogSpace

**Live Website:** [http://BlogSpaceWeb.lovestoblog.com](http://BlogSpaceWeb.lovestoblog.com)

BlogSpace is a simple PHP-based blog website built using **HTML, CSS, PHP, and MySQL**.  
It allows users to read and share blog posts, manage content from the backend, and store all data in a MySQL database.

---

## 🚀 Features
- 📰 Dynamic blog post display (from database)
- ✍ Admin panel to add, edit, delete posts
- 📅 Timestamped posts
- 📸 Image upload support
- 📱 Responsive front-end design

---

## 🧰 Tech Stack

| Layer | Technology Used |
|-------|------------------|
| **Frontend** | HTML5, CSS3, JavaScript |
| **Backend** | PHP (InfinityFree hosting) |
| **Database** | MySQL |
| **Server** | InfinityFree (Free Hosting) |
| **Local Development** | XAMPP (localhost testing) |

---

## ⚙️ Setup Instructions (Local)

1. **Install XAMPP**  
   Download from [https://www.apachefriends.org](https://www.apachefriends.org).

2. **Move the project folder**  
   Copy the `BlogSpace` folder into your `C:\xampp\htdocs\` directory.

3. **Create the database**
   - Open [http://localhost/phpmyadmin](http://localhost/phpmyadmin)
   - Create a new database (e.g., `blogspace_db`)
   - Import the `database.sql` file

4. **Update database config**
   Edit `db.php` and set:

   ```php
   $host = "localhost";
   $user = "root";
   $pass = "";
   $db   = "blogspace_db";
   ```

---

## ☁️ Hosting

**Hosting Provider:** [InfinityFree.net](https://www.infinityfree.net)  
**Live Domain:** [http://BlogSpaceWeb.lovestoblog.com](http://BlogSpaceWeb.lovestoblog.com)

### Steps:
1. Create an account at **InfinityFree**  
2. Choose a free subdomain (e.g., `BlogSpaceWeb.lovestoblog.com`)  
3. Open **File Manager** → Upload all project files into the `/htdocs` folder  
4. Create a **MySQL Database** in the InfinityFree Control Panel  
5. Open **phpMyAdmin** and import your `.sql` file  
6. Update the connection details in your PHP configuration file (`db.php`):

   ```php
   $host = "sqlXXX.epizy.com"; // Replace with your actual host
   $user = "epiz_XXXXXXXX";    // Replace with your InfinityFree DB username
   $pass = "your_db_password"; // Replace with your InfinityFree DB password
   $db   = "epiz_XXXXXXXX_db"; // Replace with your InfinityFree DB name
   ```

---

✅ **Your BlogSpace is now live!**  
Share your ideas, stories, and creativity with the world through your very own blogging platform.
