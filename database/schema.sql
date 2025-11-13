-- Apprenticeship Portal Database Schema
-- MySQL 8.0+

CREATE DATABASE IF NOT EXISTS apprenticeship_portal CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE apprenticeship_portal;

-- Table 1: users
CREATE TABLE users (
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    user_type ENUM('applicant', 'employer') NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    is_active TINYINT(1) DEFAULT 1,
    INDEX idx_email (email),
    INDEX idx_user_type (user_type)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table 2: applicant_profiles
CREATE TABLE applicant_profiles (
    profile_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    cv_path VARCHAR(500),
    bio TEXT,
    phone VARCHAR(20),
    location VARCHAR(255),
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    INDEX idx_user_id (user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table 3: employer_profiles
CREATE TABLE employer_profiles (
    employer_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    company_name VARCHAR(255) NOT NULL,
    company_description TEXT,
    contact_number VARCHAR(20),
    company_website VARCHAR(500),
    company_address TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    INDEX idx_user_id (user_id),
    INDEX idx_company_name (company_name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table 4: apprenticeships
CREATE TABLE apprenticeships (
    apprenticeship_id INT AUTO_INCREMENT PRIMARY KEY,
    employer_id INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    description TEXT NOT NULL,
    location VARCHAR(255),
    salary VARCHAR(100),
    closing_date DATE,
    is_active TINYINT(1) DEFAULT 1,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (employer_id) REFERENCES employer_profiles(employer_id) ON DELETE CASCADE,
    INDEX idx_employer_id (employer_id),
    INDEX idx_closing_date (closing_date),
    INDEX idx_is_active (is_active),
    FULLTEXT idx_search (title, description)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table 5: custom_fields
CREATE TABLE custom_fields (
    field_id INT AUTO_INCREMENT PRIMARY KEY,
    apprenticeship_id INT NOT NULL,
    field_label VARCHAR(255) NOT NULL,
    field_type ENUM('text', 'textarea', 'dropdown', 'file') NOT NULL,
    field_options TEXT, -- JSON array for dropdown options
    is_required TINYINT(1) DEFAULT 0,
    field_order INT DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (apprenticeship_id) REFERENCES apprenticeships(apprenticeship_id) ON DELETE CASCADE,
    INDEX idx_apprenticeship_id (apprenticeship_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table 6: applications
CREATE TABLE applications (
    application_id INT AUTO_INCREMENT PRIMARY KEY,
    apprenticeship_id INT NOT NULL,
    applicant_id INT NOT NULL,
    submitted_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    status ENUM('submitted', 'reviewed', 'shortlisted', 'rejected') DEFAULT 'submitted',
    notes TEXT, -- Employer notes
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (apprenticeship_id) REFERENCES apprenticeships(apprenticeship_id) ON DELETE CASCADE,
    FOREIGN KEY (applicant_id) REFERENCES applicant_profiles(profile_id) ON DELETE CASCADE,
    INDEX idx_apprenticeship_id (apprenticeship_id),
    INDEX idx_applicant_id (applicant_id),
    INDEX idx_status (status),
    UNIQUE KEY unique_application (apprenticeship_id, applicant_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table 7: application_responses
CREATE TABLE application_responses (
    response_id INT AUTO_INCREMENT PRIMARY KEY,
    application_id INT NOT NULL,
    field_id INT NOT NULL,
    response_text TEXT,
    file_path VARCHAR(500), -- For file uploads
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (application_id) REFERENCES applications(application_id) ON DELETE CASCADE,
    FOREIGN KEY (field_id) REFERENCES custom_fields(field_id) ON DELETE CASCADE,
    INDEX idx_application_id (application_id),
    INDEX idx_field_id (field_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert sample data for testing (optional)
-- Sample employer user
INSERT INTO users (name, email, password_hash, user_type) VALUES
('Tech Corp', 'employer@techcorp.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'employer');

-- Sample applicant user
INSERT INTO users (name, email, password_hash, user_type) VALUES
('John Smith', 'john@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'applicant');

-- Note: Default password for both sample users is 'password' (hashed with bcrypt)
-- Remember to change these passwords in production!
