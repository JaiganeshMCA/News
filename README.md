# News Portal

A responsive news web application built with HTML, CSS, JavaScript, Bootstrap, and PHP.

## Features

- Responsive design that works on all devices
- Featured news section
- Latest news updates
- Category-based news organization
- Search functionality
- Admin panel for content management
- User authentication system
- Comment system
- Social media integration

## Requirements

- PHP 7.4 or higher
- MySQL 5.7 or higher
- Apache/Nginx web server
- mod_rewrite enabled (for Apache)

## Installation

1. Clone the repository to your web server directory:
   ```bash
   git clone https://github.com/yourusername/news-portal.git
   ```

2. Create a MySQL database named `news_portal`

3. Import the database structure:
   ```bash
   mysql -u root -p news_portal < database/news_portal.sql
   ```

4. Configure the database connection:
   - Open `config/database.php`
   - Update the database credentials if needed:
     ```php
     define('DB_SERVER', 'localhost');
     define('DB_USERNAME', 'your_username');
     define('DB_PASSWORD', 'your_password');
     define('DB_NAME', 'news_portal');
     ```

5. Set up your web server:
   - For Apache, ensure mod_rewrite is enabled
   - Configure your virtual host to point to the project directory

6. Default admin credentials:
   - Username: admin
   - Password: password
   - Email: admin@example.com

## Directory Structure

```
news-portal/
├── assets/
│   ├── css/
│   │   └── style.css
│   ├── js/
│   │   └── main.js
│   └── images/
├── config/
│   └── database.php
├── database/
│   └── news_portal.sql
├── admin/
│   └── [admin files]
├── includes/
│   └── [PHP includes]
└── index.php
```

## Usage

1. Access the website through your web browser
2. Browse news articles by category
3. Use the search function to find specific articles
4. Log in to the admin panel to manage content

## Admin Panel

The admin panel provides the following features:
- Article management (create, edit, delete)
- Category management
- User management
- Comment moderation
- Featured articles selection

## Contributing

1. Fork the repository
2. Create your feature branch
3. Commit your changes
4. Push to the branch
5. Create a new Pull Request

## License

This project is licensed under the MIT License - see the LICENSE file for details.

## Support

For support, please open an issue in the GitHub repository or contact the maintainers.