# GirliGirl Gym & Fitness Management System
## Software Requirement Specification (SRS)

### Gym Information

**Gym Name:** GirliGirl Gym & Fitness

**Tagline:** Mymensingh's First Ever & Only Dedicated Ladies Gym

**Address:**
Reja Tower, 3rd Floor, Lift-2 (Opposite of BRAC Bank)
24/Ka, Shaymachoron Roy Road,
Notun Bazar, Mymensingh

**Phone:** 01728-381737

---

# Technology

## Backend

- PHP 8.3+
- Laravel 12 (Latest Stable)
- Laravel REST API
- Laravel Queue (Email & SMS)
- Laravel Scheduler (Membership Expiry & Notifications)

## Frontend

- Blade Template + Bootstrap 5
- Responsive Design
- AJAX (jQuery)

## Database

- MySQL 8+

## Authentication

- Laravel Authentication
- Role & Permission Management

## Server

- Ubuntu Server
- Apache / Nginx
- PHP-FPM
- SSL Support

## Third-party Integration

- ZKTeco Attendance Device
- bKash Payment Gateway 
- Nagad Payment Gateway
- SMS Gateway
- Email (SMTP)
all option set to configration setup on admin panel

## Other

- REST API Ready (reserved for future mobile app / third-party integration; no external API consumers in this version)
- PDF Invoice Generation
- QR Code Support
- Responsive Admin Panel

---

# Website

- Home
- About Us
- Membership Plans
- Personal Training
- Weight Training
- Daily Fitness Classes
- Diet & Nutrition
- Tips & Tricks
- Gallery
- Contact
- Online Registration

---

# Membership Plans

| Plan | Price |
|-------|-------|
| Admission Fee | 2,500 BDT |
| Monthly | 1,500 BDT |
| 3 Month | 4,000 BDT |
| 6 Month | 7,000 BDT + 2,000 BDT Admission (500 BDT Admission Discount applied) |
| 12 Month | 13,000 BDT (Admission Free) |
| Lifetime | 80,000 BDT one-time (Admission Free, No Recurring Due Date) |

All prices must be configurable from the Admin Panel.

**Lifetime Plan Rule:** Lifetime members have no renewal due date and remain **Active** indefinitely. Admin may still manually set a Lifetime member to **Closed** (e.g. on request or policy violation).

---

# Personal Training & Classes

Offered as add-on services alongside membership (not a replacement for membership).

- Trainer Profile (Name, Specialization, Contact, Photo)
- Personal Training Package (Sessions Count, Price, Validity)
- Class Schedule (Class Name, Trainer, Day/Time, Capacity)
- Member can be assigned to a Trainer / Package
- Session usage tracked (Sessions Used / Remaining)

Pricing for PT packages and classes is configurable from Settings, separate from membership plan pricing.

---

# Member Registration

Members can register:

- Admin Registration
- Online Registration

Online registration remains **Pending** until payment is completed.

If payment is completed through **bKash** or **Nagad**, the member is automatically approved and status becomes **Active**.

If registered manually, Admin can approve after receiving payment.

Admin can also provide a discount before confirming admission. Every manual discount must be logged with reason, amount, and the Admin who applied it, and must appear in the Discount Report.

---

# Member Information

- Admission ID (Auto Generate)
- Full Name
- Mobile Number
- Email
- Date of Birth
- Address
- NID / Birth Registration Number
- NID Image
- Member Photo
- Emergency Contact
- Admission Date

### Health Information

- Height
- Weight
- Blood Group
- Fitness Goal
- Medical Conditions / Allergies (Optional)

**Progress Tracking:** Admin/Trainer can add periodic Height & Weight updates. Full history is stored and visible to the member in the Member Portal.

---

# Membership Status

Four statuses:

- Pending
- Active
- Expired
- Closed

**Pending**
Online registration awaiting payment/approval.
No gym access. No ZKTeco user created yet.

**Active**
Member can access the gym.

**Expired**
Payment due date has passed.

Member login disabled.

Door access disabled.

Cannot enter the gym.

**Closed**
Member remains unpaid for 3 months.

Membership permanently closed.

User removed from ZKTeco device.

If the member joins again, a new admission must be created.

**Note:** Freeze/Suspend (temporary hold) is not supported in this version. A member who needs a break simply lets the membership run to Expired/Closed and re-admits later.

---

# Payment Rules

Renewal date is always fixed to the original admission date's calendar day and does not shift based on when payment is actually made — paying early or late does not change the cycle.

Example:

Admission Date

01-05-2026

Next Due Date

01-06-2026

Next Due Date

01-07-2026

Always renew on the same calendar date.

**Month-End Rule:** If the admission day does not exist in a following month (e.g. 31st in a 30-day month, or 29th/30th/31st in February), the due date falls on the last day of that month.

---

# Payment System

Support:

- Admission Fee
- Monthly Fee
- Package Payment
- Renewal Payment
- Discount
- Manual Payment
- Online Payment
- Refund

Online Payment:

- bKash
- Nagad

If payment is completed online:

- Automatically approve member
- Automatically activate membership
- Generate payment receipt

Members can also renew/pay via bKash or Nagad directly from the Member Portal, not only during initial registration.

**Refunds:** Admin can record a refund against any payment. Refunded amount is recorded as a negative transaction under the original payment account. Admin manually updates membership status if a refund cancels the membership.

Every payment must generate:

- Receipt
- Invoice (Printable)

---

# Payment Accounts

Admin can create multiple accounts.

Examples:

- Cash
- Bank
- bKash
- Nagad
- Online Wallet

Every income and expense must be recorded under an account.

No advanced accounting module is required.

---

# Attendance & Door Lock

System integrates with ZKTeco.

Support:

- Fingerprint
- RFID Card

Functions:

- Create User
- Update User
- Disable User
- Delete User
- Attendance Sync

When membership becomes **Expired**

Automatically disable user in ZKTeco.

When membership becomes **Closed**

Automatically remove user from ZKTeco.

**Sync Failure Handling:** If the ZKTeco device is offline or a sync action fails, the action is queued (Laravel Queue) and retried automatically. Admin can see pending/failed sync status in the Admin Panel.

---

# Attendance

Store:

- Check In
- Check Out
- Date
- Time
- Duration

**Missed Checkout Rule:** If a member checks in but never checks out, the system auto-checks-out at gym closing time (configurable in Settings) and calculates duration accordingly.

Attendance history should be available for every member.

---

# Member Portal

Members can view:

- Dashboard
- Membership Status
- Due Date
- Payment History (Full history, paginated)
- Attendance History
- Download Receipts

Members can update:

- Profile Photo
- Email
- Mobile Number
- Password

Members can pay:

- Renewal Payment (bKash / Nagad)

Other information can only be updated by Admin.

---

# Admin Dashboard

Dashboard should display:

- Total Members
- Active Members
- Expired Members
- Closed Members
- Today's Attendance
- Today's Collection
- Monthly Collection
- Upcoming Renewals
- Revenue Chart
- Membership Chart

---

# Offer Management

Admin can create promotional offers.

Example:

- Monthly Fee 999 BDT
- Admission Free
- 20% Discount
- 3 Month Special Package

Offer Settings:

- Offer Name
- Start Date
- End Date
- Discount Type
- Discount Amount
- Active / Inactive

---

# Notifications

Automatic SMS & Email

Renewal Reminder:

- 3 Days Before Due
- Due Date

Closed Account Reminder:

- 15 Days
- 10 Days
- 5 Days
- 1 Day
- Final Day

Also sent automatically:

- Registration/Admission Confirmation
- Payment Received Confirmation

Support:

- Bulk SMS
- Bulk Email

---

# Reports

- Admission Report
- Member Report
- Attendance Report
- Payment Report
- Due Report
- Expired Member Report
- Closed Member Report
- Collection Report
- Expense Report
- Offer Report
- Discount Report

All reports exportable as PDF and Excel.

---

# User Roles

- Super Admin
- Admin
- Reception
- Trainer (view assigned members, log PT sessions/class attendance only)

Role Permission:

- View
- Create
- Update
- Delete

---

# Settings

Configurable Settings:

- Admission Fee
- Monthly Fee
- Membership Packages
- Personal Training / Class Fees
- Membership Prefix
- Gym Closing Time (for auto-checkout)
- SMS Settings
- Email Settings
- bKash Settings
- Nagad Settings
- ZKTeco Device Settings
- Business Information
- Logo
- Invoice Footer

---

# Data Security & Privacy

- NID Images, Health Information, and payment data are considered sensitive and access-restricted by role.
- Regular database backups required.
- All data transmission over SSL.

---

# Business Rules

1. Membership renewal date is always fixed to the original admission date's calendar day, regardless of when payment is actually made (see Payment Rules & Month-End Rule).

2. If payment is overdue:
   - Status becomes **Expired**
   - Member cannot enter the gym.
   - ZKTeco access is disabled.

3. If payment remains unpaid for 3 months:
   - Status becomes **Closed**
   - Member is removed from the ZKTeco device.

4. Closed members must complete a new admission to join again.

5. Online payments (bKash/Nagad) automatically approve and activate the membership, whether at registration or renewal.

6. Every payment generates an invoice and receipt automatically.

7. Every attendance record must store check-in and check-out time; missed checkouts auto-close at gym closing time.

8. Admin can manually register members and receive manual payments.

9. All payment amounts and package prices must be configurable from Settings.

10. System should be responsive and optimized for desktop, tablet, and mobile browsers.

11. Lifetime members never expire based on payment; only Admin can manually close them.

12. Every manual discount must be logged with reason and must appear in the Discount Report.

13. If a ZKTeco sync action fails, it is queued and retried automatically; failures are visible to Admin.
