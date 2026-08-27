# Project CRUD & Database Fields Overview

This document provides a general overview of the models, CRUD modules, and database fields implemented in the project. It serves as context for any AI assistant to understand the codebase structure and schema.

---

## 1. Cities Module (`City` Model)
Manages the cities where employees and agents operate.

* **Database Table:** `cities`
* **Model File:** [`app/Models/City.php`](file:///Users/mitesh/Documents/www/GitHub/jb_work_shop/app/Models/City.php)
* **Controller:** [`app/Http/Controllers/Admin/CityController.php`](file:///Users/mitesh/Documents/www/GitHub/jb_work_shop/app/Http/Controllers/Admin/CityController.php)

### Fields Checklist:
| Field Name | Type | Description |
| :--- | :--- | :--- |
| `id` | BigInt (PK) | Unique Identifier |
| `name` | String | Name of the city |
| `status` | Enum / String | Status (`active` or `inactive`) |
| `created_at` / `updated_at` | Timestamps | Eloquent system timestamps |

---

## 2. Employees Module (`User` Model)
Manages the internal staff/employees, their system credentials, insurance policies, KYC verification, and bank account information.

* **Database Table:** `users`
* **Model File:** [`app/Models/User.php`](file:///Users/mitesh/Documents/www/GitHub/jb_work_shop/app/Models/User.php)
* **Controller:** [`app/Http/Controllers/Admin/EmployeeController.php`](file:///Users/mitesh/Documents/www/GitHub/jb_work_shop/app/Http/Controllers/Admin/EmployeeController.php)
* **Features:** Soft deletes, Spatie Role/Permission traits, insurance tracking, and file uploads.

### Fields Checklist:
| Field Name | Type | Description |
| :--- | :--- | :--- |
| `id` | BigInt (PK) | Unique Identifier |
| `name` | String | Display Name (Automatically synced from First + Middle + Last name) |
| `first_name` | String | Employee's first name |
| `middle_name` | String (Nullable) | Employee's middle name |
| `last_name` | String | Employee's last name |
| `email` | String (Unique) | Corporate/login email address |
| `password` | String | Hashed login password |
| `mobile_number` | String | Primary contact number |
| `alternate_mobile_number` | String (Nullable) | Secondary contact number |
| `city_id` | Foreign Key | Belongs to `cities.id` |
| `address` | Text (Nullable) | Residential address |
| `joining_date` | Date (Nullable) | Date of joining the company |
| `profile_photo_path` | String (Nullable) | Storage path for profile image |
| `status` | Enum / String | Account status (`active` or `inactive`) |
| `qualification` | String (Nullable) | Highest educational qualification |
| `resume_path` | String (Nullable) | Storage path for uploaded resume file |
| **KYC Fields** | | |
| `aadhaar_card_number` | String (Nullable) | 12-digit Aadhaar card number |
| `aadhaar_photo_path` | String (Nullable) | Storage path for Aadhaar photo/PDF upload |
| `pan_card_number` | String (Nullable) | 10-digit PAN card number |
| `pan_photo_path` | String (Nullable) | Storage path for PAN photo/PDF upload |
| **Bank Account Fields** | | |
| `bank_account_number` | String (Nullable) | Bank account number |
| `bank_ifsc_code` | String (Nullable) | Bank branch IFSC code |
| `bank_name` | String (Nullable) | Bank name |
| `bank_account_holder_name` | String (Nullable) | Name on the bank account |
| `bank_cheque_photo_path`| String (Nullable) | Storage path for cancelled cheque image |
| **Insurance Fields** | | |
| `insurance_policy_number`| String (Nullable) | Employee group/personal insurance policy number |
| `insurance_policy_pdf_path`| String (Nullable) | Storage path for policy document PDF |
| `insurance_start_date` | Date (Nullable) | Start date of insurance cover |
| `insurance_end_date` | Date (Nullable) | Expiration date of insurance cover |
| `deleted_at` | Timestamp (Nullable) | Soft delete timestamp |

---

## 3. Agents Module (`Agent` Model)
Manages registered independent agents/associates who bring in business/leads.

* **Database Table:** `agents`
* **Model File:** [`app/Models/Agent.php`](file:///Users/mitesh/Documents/www/GitHub/jb_work_shop/app/Models/Agent.php)
* **Controller:** [`app/Http/Controllers/Admin/AgentController.php`](file:///Users/mitesh/Documents/www/GitHub/jb_work_shop/app/Http/Controllers/Admin/AgentController.php)
* **Features:** Soft deletes, KYC verification documents, profile photos, and bank accounts.

### Fields Checklist:
| Field Name | Type | Description |
| :--- | :--- | :--- |
| `id` | BigInt (PK) | Unique Identifier |
| `first_name` | String | Agent's first name |
| `last_name` | String | Agent's last name |
| `email` | String (Unique) | Email address |
| `mobile_number` | String | Primary contact number |
| `alternate_mobile_number` | String (Nullable) | Alternate contact number |
| `city_id` | Foreign Key | Belongs to `cities.id` |
| `address` | Text (Nullable) | Office/residential address |
| `status` | Enum / String | Agent status (`active` or `inactive`) |
| `profile_photo_path` | String (Nullable) | Storage path for profile image |
| `qualification` | String (Nullable) | Educational qualification |
| `resume_path` | String (Nullable) | Storage path for resume/CV |
| **KYC Fields** | | |
| `aadhaar_card_number` | String (Nullable) | Aadhaar card number |
| `aadhaar_photo_path` | String (Nullable) | Storage path for Aadhaar doc |
| `pan_card_number` | String (Nullable) | PAN card number |
| `pan_photo_path` | String (Nullable) | Storage path for PAN doc |
| **Bank Account Fields** | | |
| `bank_account_number` | String (Nullable) | Bank account number |
| `bank_ifsc_code` | String (Nullable) | IFSC Code |
| `bank_name` | String (Nullable) | Bank name |
| `bank_account_holder_name` | String (Nullable) | Account holder's name |
| `bank_cheque_photo_path`| String (Nullable) | Cancelled cheque photo path |
| `deleted_at` | Timestamp (Nullable) | Soft delete timestamp |

---

## 4. Leads Module (`Lead` Model)
Manages potential customers and applications for loans. This is the most comprehensive data model in the system, detailing personal, educational, financial, tax (ITR), GST, and business profiles.

* **Database Table:** `leads`
* **Model File:** [`app/Models/Lead.php`](file:///Users/mitesh/Documents/www/GitHub/jb_work_shop/app/Models/Lead.php)
* **Controller:** [`app/Http/Controllers/Admin/LeadController.php`](file:///Users/mitesh/Documents/www/GitHub/jb_work_shop/app/Http/Controllers/Admin/LeadController.php)
* **Features:** Soft deletes, JSON-casted bank details, JSON-casted running loan details.

### Fields Checklist:
| Field Name | Type | Description |
| :--- | :--- | :--- |
| `id` | BigInt (PK) | Unique Identifier |
| `name` | String | Lead/Applicant name |
| `email` | String (Nullable) | Email address |
| `mobile_number` | String | Primary contact number |
| `alternate_mobile_number` | String (Nullable) | Secondary contact number |
| `agent_id` | Foreign Key (Nullable) | Belongs to `agents.id` (Agent who brought the lead) |
| `city_id` | Foreign Key | Belongs to `cities.id` |
| `source` | String (Nullable) | Source of lead generation |
| `status` | Enum / String | Lead pipeline status (e.g., `new`, `in_progress`, `approved`, etc.) |
| `notes` | Text (Nullable) | Admin/Agent remarks |
| `date_of_birth` | Date (Nullable) | Birth date |
| `gender` | Enum / String | Gender (`male`, `female`, `other`) |
| `address` | Text (Nullable) | Residential address |
| `aadhar_card` | String (Nullable) | Aadhaar number |
| `pan_card` | String (Nullable) | PAN card number |
| `udyam_registration` | String (Nullable) | Udyam MSME Registration Number |
| `education` | String (Nullable) | Applicant education |
| `mother_name` | String (Nullable) | Mother's name |
| **Taxation & ITR Fields** | | |
| `itr_id` | String (Nullable) | Income Tax Portal Login ID |
| `itr_password` | String (Nullable) | Income Tax Portal Password |
| `itr_audited` | Boolean | Whether ITR is audited |
| `itr_ay_2026_27` | Boolean | Exists / Filed for Assessment Year 2026-27 |
| `itr_ay_2025_26` | Boolean | Exists / Filed for Assessment Year 2025-26 |
| `itr_ay_2024_25` | Boolean | Exists / Filed for Assessment Year 2024-25 |
| **GST Fields** | | |
| `gst_applicable` | Boolean | Is GST applicable to business |
| `gst_number` | String (Nullable) | GSTIN Number |
| `gst_id` | String (Nullable) | GST Portal ID |
| `gst_password` | String (Nullable) | GST Portal Password |
| **Business Fields** | | |
| `business_name` | String (Nullable) | Registered business name |
| `firm_name` | String (Nullable) | Brand/Firm name |
| `constitution_of_business` | String (Nullable)| Structure (e.g., Sole Proprietorship, Partnership, Private Limited) |
| `introduction` | Text (Nullable) | Business description/summary |
| `business_address` | Text (Nullable) | Principal place of business |
| `business_activity` | String (Nullable)| Nature of work (e.g., Manufacturing, Service, Retail) |
| `business_experience` | String (Nullable)| Experience in years |
| `no_of_manpower` | Integer (Nullable) | Number of active employees/workers |
| `business_location` | String (Nullable)| Location type (Owned/Rented, Industrial/Commercial zone) |
| `area_of_premises` | String (Nullable)| Property area dimensions |
| `connectivity` | String (Nullable) | Road/Logistics connectivity info |
| **Financial & Requirements** | | |
| `bank_details` | JSON Array (Nullable)| Nested array of banks: `bank_name`, `account_no`, `ifsc`, etc. |
| `required_loan_amount` | Decimal (Nullable) | Total required funding/loan size |
| `cc_amount` | Decimal (Nullable) | Cash Credit (CC) requirement |
| `cc_details` | Text (Nullable) | Collateral/Stock details for CC |
| `term_loan_amount` | Decimal (Nullable) | Term loan portion requirement |
| `term_loan_machinery_details` | Text (Nullable)| Machinery/Asset details to be purchased |
| `current_loans` | JSON Array (Nullable)| Nested list of current liabilities: `bank`, `amount`, `emi`, etc. |
| `deleted_at` | Timestamp (Nullable) | Soft delete timestamp |

---

## 5. Roles & Access Control Module (`Role` Model)
Handles Role-Based Access Control (RBAC) extending Spatie's permission package.

* **Database Table:** `roles`
* **Model File:** [`app/Models/Role.php`](file:///Users/mitesh/Documents/www/GitHub/jb_work_shop/app/Models/Role.php)
* **Controller:** [`app/Http/Controllers/Admin/RoleController.php`](file:///Users/mitesh/Documents/www/GitHub/jb_work_shop/app/Http/Controllers/Admin/RoleController.php)

### Fields Checklist:
| Field Name | Type | Description |
| :--- | :--- | :--- |
| `id` | BigInt (PK) | Unique Identifier |
| `name` | String | Unique Role Name (e.g., `Admin`, `Employee`) |
| `guard_name` | String | Authentication guard (e.g., `web`) |
| `description` | String (Nullable) | Friendly explanation of what this role does |
| `is_active` | Boolean | Whether the role is active (Deactivated roles grant no permissions) |
