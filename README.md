# 🎓 Event Management System

## 📋 Overview

NIBM Unity is a comprehensive web platform designed to connect NIBM students, manage campus events, facilitate ticket purchasing, and enhance the overall campus experience. This platform serves as a central hub for student engagement, event management, and community building at NIBM.

## ✨ Features

### 🧑‍🎓 User Management

- User registration and authentication
- Profile creation and management
- Role-based access control (Students, Admins)

### 📅 Event Management

- Browse upcoming and past events
- Event details with descriptions, dates, and locations
- Event attendance tracking
- Like and interact with events

### 🎟️ Ticket System

- Purchase tickets for paid events
- Ticket confirmation and validation
- Digital ticket management

### 👨‍💼 Administration

- Admin dashboard for site management
- User management capabilities
- Event creation and management tools
- Analytics and reporting

## 🛠️ Technology Stack

- **Frontend**: HTML, CSS, JavaScript, Bootstrap
- **Backend**: PHP
- **Database**: MySQL
- **Additional Libraries**: jQuery

## 🚀 Getting Started

### Prerequisites

- XAMPP (or similar PHP development environment)
- MySQL Database
- Web Browser

### Installation

1. **Clone the repository**

   ```
   git clone https://github.com/yourusername/nibm-unity.git
   ```

2. **Set up the database**

   - Start MySQL server from XAMPP control panel
   - Create a new database named "nibm_unity"
   - Import the database schema from `DOCS/Database.sql`

3. **Configure database connection**

   - Update database credentials in `includes/db.php`

4. **Start the application**
   - Start Apache server from XAMPP control panel
   - Access the application at `http://localhost/nibm-unity/`

## 📂 Project Structure

```
├── about.php               # About page
├── contact.php             # Contact information page
├── index.php               # Homepage
├── admin/                  # Admin portal
│   ├── dashboard.php       # Admin dashboard
│   ├── manage_events.php   # Event management
│   └── manage_users.php    # User management
├── ajax/                   # AJAX functionalities
├── assets/                 # Static resources
│   ├── css/                # Stylesheets
│   └── js/                 # JavaScript files
├── DOCS/                   # Documentation
├── events/                 # Event-related features
├── includes/               # Reusable components
├── tickets/                # Ticketing system
└── user/                   # User authentication & profiles
```

## 👥 User Roles

### 📚 Students

- Register and manage profile
- Browse and attend events
- Purchase tickets
- Interact with event content

### 🛡️ Administrators

- Manage all platform content
- Create and edit events
- Manage user accounts
- Access analytics dashboard

## 🔄 Workflow

1. Users register/login to the platform
2. Browse upcoming events on the homepage or events listing
3. View detailed information about specific events
4. Register for events or purchase tickets
5. Attend events and participate in campus activities
6. Administrators manage content through the admin dashboard

## 📱 Screenshots

_[Add screenshots of key pages here]_

## 🤝 Contributing

Contributions to improve NIBM Unity are welcome. Please follow these steps:

1. Fork the repository
2. Create a feature branch (`git checkout -b feature/new-feature`)
3. Commit your changes (`git commit -m 'Add new feature'`)
4. Push to the branch (`git push origin feature/new-feature`)
5. Open a Pull Request

## ⚠️ Known Issues

_[List any known issues or limitations here]_

## 📞 Support

For support, please contact the development team at `support@nibmunity.edu` or open an issue in the project repository.

## 📜 License

This project is licensed under the MIT License - see the LICENSE file for details.

## 🙏 Acknowledgements

- NIBM Faculty and Administration
- Student Contributors
- [List any libraries or resources used]
