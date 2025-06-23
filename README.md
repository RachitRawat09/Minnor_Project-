# CodeToCuisine 🍽️


A modern, interactive restaurant menu and order management web app for both customers and admins.  
Built with PHP, MySQL, Bootstrap, and JavaScript.

---

## 🚀 Features

- **Customer Portal**
  - Browse menu and categories
  - Add items to cart and customize orders
  - Place and track orders in real-time
  - View and print bills
  - Multiple payment options (Cash/Online)

- **Admin Portal**
  - Manage menu items and categories
  - Track and update order statuses
  - View sales analytics and dashboard
  - Expense tracking

- **Other**
  - Responsive design (mobile & desktop)
  - SweetAlert2 for beautiful popups
  - Secure authentication for admins

---

## 🖥️ Demo

Scan me to see the live preview of project 

![Customer Menu Screenshot](assets/images/frame.png)
<!-- ![Admin Dashboard Screenshot](assets/images/admin_dashboard.png) -->

---
## work flow 

This is customer side from where you can browse food items and place your order...including detaisl like table number and mobile number.
Mobile number is required to track your food.You can also download your e-bill ... for paperless work.

These whole data will seen by admin panel...i also have created full fledge admin pannel with the features manage menu, order history, sale tracking system etc.

## 📦 Installation

1. **Clone the repository**
   ```bash
   git clone https://github.com/yourusername/Minnor_Project-.git
   cd Minnor_Project-
   ```

2. **Set up the database**
   - Import the provided SQL file (if any) into your MySQL server.
   - Update `includes/db_connect.php` with your DB credentials.

3. **Configure XAMPP/MAMP/WAMP**
   - Place the project folder in your web server's root directory (e.g., `htdocs` for XAMPP).
   - Make sure PHP and MySQL are running.

4. **Access the app**
   - Open your browser and go to:  
     `http://localhost/Minnor_Project-/customer/` for customers  
     `http://localhost/Minnor_Project-/admin/` for admins

---

## ⚙️ Project Structure

```plaintext
Minnor_Project-/
  ├── admin/         # Admin dashboard, menu, orders, expenses
  ├── customer/      # Customer-facing menu, cart, order tracking
  ├── includes/      # Shared PHP includes (DB, header, footer)
  ├── assets/        # CSS, images, static files
  ├── uploads/       # Uploaded menu images
  ├── error.php      # Custom error page
  └── index.php      # Entry point
```

---

## 📝 Usage

- **Customer:**  
  Browse menu, add to cart, place order, track status, pay, and print bill.
- **Admin:**  
  Log in, manage menu, view and update orders, track expenses, and view analytics.

---

## 🛡️ Security

- Passwords are hashed using bcrypt.
- Admin routes are protected by session checks.
- Input validation and prepared statements used for DB queries.

---

## 🤝 Contributing

1. Fork this repo
2. Create your feature branch (`git checkout -b feature/YourFeature`)
3. Commit your changes (`git commit -am 'Add some feature'`)
4. Push to the branch (`git push origin feature/YourFeature`)
5. Open a Pull Request

---


---


---

> _If you use this project, please ⭐ star the repo and share your feedback!_
