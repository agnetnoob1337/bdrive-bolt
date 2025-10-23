# BDrive - File Management System with MySQL

BDrive is a PHP-based file management system with MySQL database integration for tracking files and tags.

## Features

- File upload with automatic database tracking
- Tag-based file organization
- Search by filename or tags
- Folder management (create, rename, delete)
- File operations (rename, delete, download)
- Tag editing in the file info panel
- Recursive folder scanning
- Image and video preview with lazy loading

## Requirements

- PHP 7.4 or higher
- MySQL 5.7 or higher
- Apache or Nginx web server
- MySQLi extension enabled

## Installation

1. **Database Setup**
   - Create a MySQL database named `bdrive`
   - Import the database schema:
     ```bash
     mysql -u root -p < setup.sql
     ```
   - Or manually run the SQL commands in `setup.sql`

2. **Configure Database Connection**
   - Edit `db_config.php` and update the database credentials:
     ```php
     $db_host = 'localhost';
     $db_user = 'your_username';
     $db_pass = 'your_password';
     $db_name = 'bdrive';
     ```

3. **Create Files Directory**
   - Create a `files` directory in the project root
   - Ensure it has write permissions:
     ```bash
     mkdir files
     chmod 755 files
     ```

4. **Set Up Web Server**
   - Point your web server document root to the project directory
   - Ensure PHP is configured to handle `.php` files

## Usage

1. **Login**
   - Default credentials: `admin` / `password`
   - Update credentials in `Index.php` for security

2. **Upload Files**
   - Select files using the file input
   - Click "Upload Files"
   - Files are automatically added to the database

3. **Search Files**
   - Use the search bar to find files by name or tags
   - Search works across both filename and tag fields

4. **Manage Tags**
   - Click "Info" on any file card
   - Click the edit button (✏️) next to Tags
   - Enter comma-separated tags
   - Click save (💾) to update

5. **Folder Management**
   - Click "⚙️ Folders" to show folder controls
   - Create new folders
   - Rename or delete existing folders
   - Navigate folders by clicking folder buttons

## Database Schema

The `files` table contains:
- `id`: Auto-incrementing primary key
- `filename`: Name of the file
- `filepath`: Full path to the file (unique)
- `filetype`: Category (image, video, document, audio, other)
- `tags`: Comma-separated tags
- `upload_date`: Timestamp of upload

## How It Works

1. **File Upload**: Files are uploaded to the filesystem and metadata is stored in MySQL
2. **File Loading**: Files are loaded from database queries based on folder path
3. **Search**: Searches both filename and tags fields in the database
4. **Delete**: Removes file from both filesystem and database
5. **Rename**: Updates both filesystem and database records
6. **Folder Operations**: Updates all affected file paths in the database

## Security Notes

- Change default login credentials in production
- Restrict file upload types as needed
- Validate all user inputs
- Use prepared statements (already implemented) to prevent SQL injection
- Set appropriate file and directory permissions
