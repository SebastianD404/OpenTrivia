# PQMS Project

## Overview
The PQMS (Patient Queue Management System) is a web application designed to streamline the patient check-in process and manage patient queues efficiently. This application provides a user-friendly interface for both patients and administrators.

## Project Structure
```
pqms
├── css
│   └── style.css
├── js
│   └── main.js
├── dist
│   ├── imageuploadify.min.css
│   └── imageuploadify.min.js
├── index.html
├── dashboard.html
├── checkin.html
├── queue.html
└── admin.html
```

## File Descriptions

- **index.html**: The landing page for the application, providing an introduction and navigation to other sections.
- **dashboard.html**: The main dashboard where users can access various features of the application.
- **checkin.html**: A dedicated page for patient check-in, allowing users to input patient information.
- **queue.html**: Displays the queue of patients, showing their status and order.
- **admin.html**: Provides the admin interface for managing the application.

## CSS and JavaScript
- **css/style.css**: Contains custom CSS styles for the application, ensuring a consistent look and feel across all pages.
- **js/script.js**: Includes the main JavaScript functionality for the application, handling user interactions and dynamic content updates.

## Dependencies
- **dist/imageuploadify.min.css**: A minified CSS file for the ImageUploadify plugin, used for enhanced file upload functionality.
- **dist/imageuploadify.min.js**: A minified JavaScript file for the ImageUploadify plugin, providing the necessary scripts for file upload features.

## Getting Started
To get started with the PQMS project, clone the repository and open the `index.html` file in your web browser. Ensure that all dependencies are correctly linked for optimal functionality.

## There are 2 ways or running this application


## 1. Installation using migration.php

1. Download the zipfolder:
```bash
   Extract the zip folder to the htdocs folder of your XAMPP installation or wamp www folder.
```

2. Navigate to the project directory:
```bash
   cd htdocs/pqms or wamp/www/pqms
```

3. Open the db_connect.php file and update the database credentials according to your mysql database configuration:
```bash
   $host = "localhost";
   $username = "root";
   $password = "";
   $database = "pqms";
```

6. Start the wamp server or xampp server and open the application in your web browser:
```bash
   Open http://localhost/pqms
```

7. Login with the following credentials or use any other creditials in the migration.php file:
```bash
   username: mvumapatrick@gmail.com
   password: 1234554321
```
This approach automatically creates metadata for the application and your good to go!


## 2. Installation using db_pqms.sql file

1. Download the zipfolder:
```bash
   Extract the zip folder to the htdocs folder of your XAMPP installation or wamp www folder.
```

2. You can also create the database using phpMyAdmin or any other database management tool.
```bash
   CREATE DATABASE pqms;
```

3. Import the database schema available in the root folder of the project db directory:
```bash
   mysql -u root -p pqms < db/pqms.sql
```

4. Change database credentials in php/db_connect.php:
```bash
   $host = "localhost";
   $username = "root";
   $password = "";
   $database = "pqms";
```

5. Start the wamp server or xampp server:
```bash
   Open http://localhost/pqms in your web browser to view the application.
```
