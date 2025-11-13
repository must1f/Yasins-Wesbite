# **Production Document: Apprenticeship Portal Website**

## **1. Overview**

This project aims to create a dual-portal website for **apprenticeship recruitment**, designed to connect employers with potential apprentices. The site will have two distinct user portals:

* **Employer Portal** – for businesses to post and manage apprenticeship listings.
* **Applicant Portal** – for users to register, upload CVs, and apply for opportunities.

The platform will run on a **LAMP stack (Linux, Apache, MySQL, PHP)** and be hosted on **34SP**, with deployment handled via **FileZilla**.
The front end will be developed using **HTML, CSS, JavaScript, and Tailwind CSS** for a responsive and modern interface.

---

## **2. Objectives**

1. Develop a secure and intuitive online platform for managing apprenticeship postings and applications.
2. Enable employers to create apprenticeship listings with custom application fields.
3. Allow applicants to register, upload CVs, and apply for available apprenticeships.
4. Provide a responsive and accessible design for all devices.
5. Implement user authentication and role-based dashboards for applicants and employers.
6. Store and retrieve all data securely from a central MySQL database.

---

## **3. System Architecture**

**Technology Stack:**

* **Frontend:** HTML5, CSS3, JavaScript (for interactivity and dynamic rendering), Tailwind CSS.
* **Backend:** PHP 8+ for server-side logic and database interaction.
* **Database:** MySQL 8.
* **Server:** Apache (via 34SP hosting).
* **Deployment:** FileZilla (FTP for file management and deployment).
* **Version Control:** GitHub or GitLab (recommended).

---

## **4. Core Features**

### **4.1 Applicant Portal**

**Features:**

* Register and log in securely.
* Create and update a user profile.
* Upload CVs (PDF, DOCX formats).
* Browse apprenticeship listings by category, location, and employer.
* Apply for apprenticeships via dynamic forms defined by employers.
* Track the status of applications from the dashboard.

**Data stored:**

* User ID, name, email, hashed password.
* Uploaded CV path.
* Application history and statuses.

---

### **4.2 Employer Portal**

**Features:**

* Register and log in securely.
* Create, edit, and delete apprenticeship listings.
* Add custom fields to each apprenticeship application form (e.g. text fields, dropdowns, or file uploads).
* View submitted applications and download applicant CVs.
* Shortlist or reject candidates directly through the dashboard.

**Data stored:**

* Employer ID, company name, email, hashed password.
* Apprenticeship details (title, description, location, pay, deadlines).
* Custom field metadata and associated applications.

---

### **4.3 Administrator (optional future feature)**

**Features:**

* Manage user accounts.
* Moderate postings and remove inappropriate content.
* Generate site analytics (e.g., total applicants, active listings).

---

## **5. Database Design (MySQL)**

### **Tables**

1. **users**

   * `user_id` (PK, INT, AUTO_INCREMENT)
   * `name` (VARCHAR)
   * `email` (VARCHAR, UNIQUE)
   * `password_hash` (VARCHAR)
   * `user_type` (ENUM: 'applicant', 'employer')
   * `created_at` (DATETIME)

2. **applicant_profiles**

   * `profile_id` (PK)
   * `user_id` (FK → users.user_id)
   * `cv_path` (VARCHAR)
   * `bio` (TEXT)

3. **employer_profiles**

   * `employer_id` (PK)
   * `user_id` (FK → users.user_id)
   * `company_name` (VARCHAR)
   * `company_description` (TEXT)
   * `contact_number` (VARCHAR)

4. **apprenticeships**

   * `apprenticeship_id` (PK)
   * `employer_id` (FK → employer_profiles.employer_id)
   * `title` (VARCHAR)
   * `description` (TEXT)
   * `location` (VARCHAR)
   * `salary` (VARCHAR)
   * `closing_date` (DATE)
   * `created_at` (DATETIME)

5. **custom_fields**

   * `field_id` (PK)
   * `apprenticeship_id` (FK → apprenticeships.apprenticeship_id)
   * `field_label` (VARCHAR)
   * `field_type` (ENUM: 'text', 'textarea', 'dropdown', 'file')

6. **applications**

   * `application_id` (PK)
   * `apprenticeship_id` (FK → apprenticeships.apprenticeship_id)
   * `applicant_id` (FK → applicant_profiles.profile_id)
   * `submitted_at` (DATETIME)
   * `status` (ENUM: 'submitted', 'reviewed', 'shortlisted', 'rejected')

7. **application_responses**

   * `response_id` (PK)
   * `application_id` (FK → applications.application_id)
   * `field_id` (FK → custom_fields.field_id)
   * `response_text` (TEXT)

---

## **6. User Flow**

### **Applicant Journey**

1. Register and log in.
2. Upload CV and complete profile.
3. Browse and filter available apprenticeships.
4. Select an apprenticeship → dynamic form loads based on employer's custom fields.
5. Submit application.
6. Track progress from dashboard.

### **Employer Journey**

1. Register and log in.
2. Create apprenticeship listing with custom form fields.
3. Manage active postings (edit/delete).
4. Review and filter applications.
5. Download CVs and shortlist candidates.

---

## **7. Frontend Design**

**Framework:** Tailwind CSS for responsive, modern styling.
**JavaScript Functionality:**

* Dynamic rendering of custom application forms.
* Real-time validation for form inputs.
* AJAX calls for submitting data asynchronously (no full-page reloads).
* Interactive dashboards (e.g., collapsible lists, modal pop-ups).

**Pages:**

1. **Landing Page:** Overview, login/registration links.
2. **Applicant Section:**

   * Dashboard (Applications, Profile, CV upload)
   * Apprenticeship listings page
   * Dynamic application form
3. **Employer Section:**

   * Dashboard (Manage Apprenticeships, View Applications)
   * New apprenticeship form
4. **Shared Components:**

   * Navigation bar
   * Footer
   * Notification banners

---

## **8. Backend Functionality (PHP)**

* **Authentication:**
  PHP sessions for login persistence, bcrypt password hashing.
* **File Uploads:**
  CVs stored in `/uploads/cv/` with sanitised filenames.
* **Dynamic Forms:**
  PHP retrieves field data from `custom_fields` and passes it to JavaScript for dynamic rendering.
* **CRUD Operations:**
  Employers can create, update, and delete apprenticeship records.
* **Validation:**
  Both client-side (JavaScript) and server-side (PHP).
* **Security Measures:**

  * Prepared SQL statements (prevent SQL injection).
  * Sanitisation of user inputs.
  * Access restrictions based on user role.

---

## **9. Hosting and Deployment**

**Host:** [34SP.com](https://www.34sp.com/)
**Deployment Process:**

1. Upload project files via **FileZilla FTP**.
2. Configure Apache and PHP on 34SP environment.
3. Import MySQL schema using **phpMyAdmin**.
4. Set file permissions (especially `/uploads/`).
5. Configure `config.php` with live database credentials.
6. Test live functionality and SSL (if provided by host).

---

## **10. Testing and Quality Assurance**

**Testing Categories:**

* **Functional Testing:** Ensure all core flows (register, login, upload, apply) work.
* **Form Validation:** Test JavaScript and PHP validation together.
* **Security Testing:** Verify sanitisation and session handling.
* **Responsive Design Testing:** Test across desktop, tablet, and mobile using Tailwind's responsive utilities.
* **User Testing:** Gather feedback from trial employers and applicants.

---

## **11. Future Enhancements**

* Email notifications for application events.
* Password reset and verification system.
* Admin analytics and reports.
* Skill-based application matching.
* REST API for third-party job board integration.

---
