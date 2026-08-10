<?php

use App\Http\Controllers\Admin\AttendanceController;
use App\Http\Controllers\Admin\BillController;
use App\Http\Controllers\Admin\BulkNotificationController;
use App\Http\Controllers\Admin\ContactMessageController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\ExpenseController;
use App\Http\Controllers\Admin\GalleryController;
use App\Http\Controllers\Admin\GymClassController;
use App\Http\Controllers\Admin\MemberController as AdminMemberController;
use App\Http\Controllers\Admin\MembershipPlanController;
use App\Http\Controllers\Admin\MemberTrainingPackageController;
use App\Http\Controllers\Admin\OfferController;
use App\Http\Controllers\Admin\PaymentController as AdminPaymentController;
use App\Http\Controllers\Admin\PersonalTrainingPackageController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Admin\TrainerController;
use App\Http\Controllers\Admin\ZKTecoSyncLogController;
use App\Http\Controllers\Auth\MemberLoginController;
use App\Http\Controllers\Auth\StaffLoginController;
use App\Http\Controllers\Member\AttendanceHistoryController as MemberAttendanceHistoryController;
use App\Http\Controllers\Member\DashboardController as MemberDashboardController;
use App\Http\Controllers\Member\OnlineRenewalController;
use App\Http\Controllers\Member\PaymentHistoryController as MemberPaymentHistoryController;
use App\Http\Controllers\Member\ProfileController as MemberProfileController;
use App\Http\Controllers\MemberRegistrationController;
use App\Http\Controllers\PublicController;
use Illuminate\Support\Facades\Route;

// Public marketing website
Route::get('/', [PublicController::class, 'home'])->name('home');
Route::get('/about', [PublicController::class, 'about'])->name('about');
Route::get('/membership-plans', [PublicController::class, 'membershipPlans'])->name('membership-plans');
Route::get('/personal-training', [PublicController::class, 'personalTraining'])->name('personal-training');
Route::get('/weight-training', [PublicController::class, 'weightTraining'])->name('weight-training');
Route::get('/classes', [PublicController::class, 'classes'])->name('classes');
Route::get('/diet-nutrition', [PublicController::class, 'dietNutrition'])->name('diet-nutrition');
Route::get('/tips-tricks', [PublicController::class, 'tipsTricks'])->name('tips-tricks');
Route::get('/gallery', [PublicController::class, 'gallery'])->name('gallery');
Route::get('/contact', [PublicController::class, 'contact'])->name('contact');
Route::post('/contact', [PublicController::class, 'contactStore'])->name('contact.store');

// Public online registration
Route::get('/register', [MemberRegistrationController::class, 'create'])->name('register.create');
Route::post('/register', [MemberRegistrationController::class, 'store'])->name('register.store');
Route::get('/register/pending', [MemberRegistrationController::class, 'pending'])->name('register.pending');

// Staff (Admin Panel) authentication
Route::middleware('guest:web')->group(function () {
    Route::get('/login', [StaffLoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [StaffLoginController::class, 'login']);
});
Route::post('/logout', [StaffLoginController::class, 'logout'])->middleware('auth:web')->name('logout');

// Member Portal authentication
Route::middleware('guest:member')->group(function () {
    Route::get('/member/login', [MemberLoginController::class, 'showLoginForm'])->name('member.login');
    Route::post('/member/login', [MemberLoginController::class, 'login']);
});
Route::post('/member/logout', [MemberLoginController::class, 'logout'])->middleware('member.auth:member')->name('member.logout');

// Admin Panel
Route::middleware(['auth:web'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');

    Route::middleware('permission:members.view')->group(function () {
        Route::get('/members', [AdminMemberController::class, 'index'])->name('members.index');
    });
    Route::middleware('permission:members.create')->group(function () {
        Route::get('/members/create', [AdminMemberController::class, 'create'])->name('members.create');
        Route::post('/members', [AdminMemberController::class, 'store'])->name('members.store');
    });
    Route::middleware('permission:members.update')->group(function () {
        Route::get('/members/{member}/edit', [AdminMemberController::class, 'edit'])->name('members.edit');
        Route::put('/members/{member}', [AdminMemberController::class, 'update'])->name('members.update');
        Route::post('/members/{member}/close', [AdminMemberController::class, 'close'])->name('members.close');
    });
    Route::middleware('permission:members.delete')->group(function () {
        Route::delete('/members/{member}', [AdminMemberController::class, 'destroy'])->name('members.destroy');
    });
    Route::middleware('permission:members.view')->group(function () {
        Route::get('/members/{member}', [AdminMemberController::class, 'show'])->name('members.show');
        Route::get('/members/{member}/print', [AdminMemberController::class, 'print'])->name('members.print');
    });

    // Member Action Panels (AJAX modal fragments, shown from the Members index dropdown)
    Route::middleware('permission:payments.create')->group(function () {
        Route::get('/members/{member}/pay-due', [AdminMemberController::class, 'payDuePanel'])->name('members.pay-due');
    });
    Route::middleware('permission:payments.view')->group(function () {
        Route::get('/members/{member}/payments-panel', [AdminMemberController::class, 'paymentsPanel'])->name('members.payments-panel');
    });
    Route::middleware('permission:members.view')->group(function () {
        Route::get('/members/{member}/attendance-panel', [AdminMemberController::class, 'attendancePanel'])->name('members.attendance-panel');
    });
    Route::middleware('permission:personal_training.view')->group(function () {
        Route::get('/members/{member}/training-panel', [AdminMemberController::class, 'trainingPanel'])->name('members.training-panel');
    });
    Route::middleware('permission:settings.view')->group(function () {
        Route::get('/members/{member}/zkteco-panel', [AdminMemberController::class, 'zkTecoPanel'])->name('members.zkteco-panel');
    });

    // Membership Plans
    Route::middleware('permission:membership_plans.view')->group(function () {
        Route::get('/membership-plans', [MembershipPlanController::class, 'index'])->name('membership-plans.index');
    });
    Route::middleware('permission:membership_plans.create')->group(function () {
        Route::get('/membership-plans/create', [MembershipPlanController::class, 'create'])->name('membership-plans.create');
        Route::post('/membership-plans', [MembershipPlanController::class, 'store'])->name('membership-plans.store');
    });
    Route::middleware('permission:membership_plans.update')->group(function () {
        Route::get('/membership-plans/{membershipPlan}/edit', [MembershipPlanController::class, 'edit'])->name('membership-plans.edit');
        Route::put('/membership-plans/{membershipPlan}', [MembershipPlanController::class, 'update'])->name('membership-plans.update');
    });
    Route::middleware('permission:membership_plans.delete')->group(function () {
        Route::delete('/membership-plans/{membershipPlan}', [MembershipPlanController::class, 'destroy'])->name('membership-plans.destroy');
    });

    // Offers
    Route::middleware('permission:offers.view')->group(function () {
        Route::get('/offers', [OfferController::class, 'index'])->name('offers.index');
    });
    Route::middleware('permission:offers.create')->group(function () {
        Route::get('/offers/create', [OfferController::class, 'create'])->name('offers.create');
        Route::post('/offers', [OfferController::class, 'store'])->name('offers.store');
    });
    Route::middleware('permission:offers.update')->group(function () {
        Route::get('/offers/{offer}/edit', [OfferController::class, 'edit'])->name('offers.edit');
        Route::put('/offers/{offer}', [OfferController::class, 'update'])->name('offers.update');
    });
    Route::middleware('permission:offers.delete')->group(function () {
        Route::delete('/offers/{offer}', [OfferController::class, 'destroy'])->name('offers.destroy');
    });

    // Trainers (staff accounts)
    Route::middleware('permission:users.view')->group(function () {
        Route::get('/trainers', [TrainerController::class, 'index'])->name('trainers.index');
    });
    Route::middleware('permission:users.create')->group(function () {
        Route::get('/trainers/create', [TrainerController::class, 'create'])->name('trainers.create');
        Route::post('/trainers', [TrainerController::class, 'store'])->name('trainers.store');
    });
    Route::middleware('permission:users.update')->group(function () {
        Route::get('/trainers/{trainer}/edit', [TrainerController::class, 'edit'])->name('trainers.edit');
        Route::put('/trainers/{trainer}', [TrainerController::class, 'update'])->name('trainers.update');
    });
    Route::middleware('permission:users.delete')->group(function () {
        Route::delete('/trainers/{trainer}', [TrainerController::class, 'destroy'])->name('trainers.destroy');
    });

    // Personal Training Packages
    Route::middleware('permission:personal_training.view')->group(function () {
        Route::get('/personal-training-packages', [PersonalTrainingPackageController::class, 'index'])->name('personal-training-packages.index');
    });
    Route::middleware('permission:personal_training.create')->group(function () {
        Route::get('/personal-training-packages/create', [PersonalTrainingPackageController::class, 'create'])->name('personal-training-packages.create');
        Route::post('/personal-training-packages', [PersonalTrainingPackageController::class, 'store'])->name('personal-training-packages.store');
    });
    Route::middleware('permission:personal_training.update')->group(function () {
        Route::get('/personal-training-packages/{personalTrainingPackage}/edit', [PersonalTrainingPackageController::class, 'edit'])->name('personal-training-packages.edit');
        Route::put('/personal-training-packages/{personalTrainingPackage}', [PersonalTrainingPackageController::class, 'update'])->name('personal-training-packages.update');
    });
    Route::middleware('permission:personal_training.delete')->group(function () {
        Route::delete('/personal-training-packages/{personalTrainingPackage}', [PersonalTrainingPackageController::class, 'destroy'])->name('personal-training-packages.destroy');
    });

    // Class Schedule
    Route::middleware('permission:personal_training.view')->group(function () {
        Route::get('/classes', [GymClassController::class, 'index'])->name('classes.index');
    });
    Route::middleware('permission:personal_training.create')->group(function () {
        Route::get('/classes/create', [GymClassController::class, 'create'])->name('classes.create');
        Route::post('/classes', [GymClassController::class, 'store'])->name('classes.store');
    });
    Route::middleware('permission:personal_training.update')->group(function () {
        Route::get('/classes/{class}/edit', [GymClassController::class, 'edit'])->name('classes.edit');
        Route::put('/classes/{class}', [GymClassController::class, 'update'])->name('classes.update');
    });
    Route::middleware('permission:personal_training.delete')->group(function () {
        Route::delete('/classes/{class}', [GymClassController::class, 'destroy'])->name('classes.destroy');
    });

    // Member Training Package Assignment
    Route::middleware('permission:personal_training.create')->group(function () {
        Route::post('/members/{member}/training-packages', [MemberTrainingPackageController::class, 'store'])->name('members.training-packages.store');
    });
    Route::middleware('permission:personal_training.update')->group(function () {
        Route::post('/training-packages/{trainingPackage}/log-session', [MemberTrainingPackageController::class, 'logSession'])->name('training-packages.log-session');
    });

    // Bills
    Route::middleware('permission:bills.view')->group(function () {
        Route::get('/bills', [BillController::class, 'index'])->name('bills.index');
        Route::get('/bills/{bill}/view-panel', [BillController::class, 'viewPanel'])->name('bills.view-panel');
    });
    Route::middleware('permission:bills.create')->group(function () {
        Route::get('/bills/create-panel', [BillController::class, 'createPanel'])->name('bills.create-panel');
        Route::get('/bills/members-search', [BillController::class, 'membersSearch'])->name('bills.members-search');
        Route::post('/bills', [BillController::class, 'store'])->name('bills.store');
        Route::get('/bills/{bill}/pay-panel', [BillController::class, 'payPanel'])->name('bills.pay-panel');
        Route::post('/bills/{bill}/pay', [BillController::class, 'pay'])->name('bills.pay');
    });
    Route::middleware('permission:bills.delete')->group(function () {
        Route::delete('/bills/{bill}', [BillController::class, 'destroy'])->name('bills.destroy');
    });

    // Payments
    Route::middleware('permission:payments.create')->group(function () {
        Route::post('/members/{member}/payments', [AdminPaymentController::class, 'store'])->name('members.payments.store');
    });
    Route::middleware('permission:payments.update')->group(function () {
        Route::post('/payments/{payment}/refund', [AdminPaymentController::class, 'refund'])->name('payments.refund');
    });
    Route::middleware('permission:payments.view')->group(function () {
        Route::get('/payments/{payment}/receipt', [AdminPaymentController::class, 'receipt'])->name('payments.receipt');
    });

    // Attendance
    Route::middleware('permission:attendance.create')->group(function () {
        Route::post('/members/{member}/attendance/check-in', [AttendanceController::class, 'checkIn'])->name('members.attendance.check-in');
        Route::post('/members/{member}/attendance/check-out', [AttendanceController::class, 'checkOut'])->name('members.attendance.check-out');
    });

    // ZKTeco Device Sync
    Route::middleware('permission:settings.view')->group(function () {
        Route::get('/zkteco-sync-logs', [ZKTecoSyncLogController::class, 'index'])->name('zkteco-sync-logs.index');
    });
    Route::middleware('permission:settings.update')->group(function () {
        Route::post('/zkteco-sync-logs/{syncLog}/retry', [ZKTecoSyncLogController::class, 'retry'])->name('zkteco-sync-logs.retry');
    });

    // Bulk Notifications
    Route::middleware('permission:members.update')->group(function () {
        Route::get('/bulk-notifications', [BulkNotificationController::class, 'create'])->name('bulk-notifications.create');
        Route::post('/bulk-notifications', [BulkNotificationController::class, 'store'])->name('bulk-notifications.store');
    });

    // Expenses
    Route::middleware('permission:payments.view')->group(function () {
        Route::get('/expenses', [ExpenseController::class, 'index'])->name('expenses.index');
    });
    Route::middleware('permission:payments.create')->group(function () {
        Route::get('/expenses/create', [ExpenseController::class, 'create'])->name('expenses.create');
        Route::post('/expenses', [ExpenseController::class, 'store'])->name('expenses.store');
    });
    Route::middleware('permission:payments.update')->group(function () {
        Route::get('/expenses/{expense}/edit', [ExpenseController::class, 'edit'])->name('expenses.edit');
        Route::put('/expenses/{expense}', [ExpenseController::class, 'update'])->name('expenses.update');
    });
    Route::middleware('permission:payments.delete')->group(function () {
        Route::delete('/expenses/{expense}', [ExpenseController::class, 'destroy'])->name('expenses.destroy');
    });

    // Reports
    Route::middleware('permission:reports.view')->group(function () {
        Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
        Route::get('/reports/admissions', [ReportController::class, 'admissions'])->name('reports.admissions');
        Route::get('/reports/members', [ReportController::class, 'members'])->name('reports.members');
        Route::get('/reports/attendance', [ReportController::class, 'attendance'])->name('reports.attendance');
        Route::get('/reports/payments', [ReportController::class, 'payments'])->name('reports.payments');
        Route::get('/reports/due', [ReportController::class, 'due'])->name('reports.due');
        Route::get('/reports/expired-members', [ReportController::class, 'expiredMembers'])->name('reports.expired-members');
        Route::get('/reports/closed-members', [ReportController::class, 'closedMembers'])->name('reports.closed-members');
        Route::get('/reports/collection', [ReportController::class, 'collection'])->name('reports.collection');
        Route::get('/reports/expenses', [ReportController::class, 'expenses'])->name('reports.expenses');
        Route::get('/reports/offers', [ReportController::class, 'offers'])->name('reports.offers');
        Route::get('/reports/discounts', [ReportController::class, 'discounts'])->name('reports.discounts');
    });

    // Settings
    Route::middleware('permission:settings.view')->group(function () {
        Route::get('/settings', [SettingController::class, 'index'])->name('settings.index');
        Route::get('/settings/zkteco/users', [SettingController::class, 'zktecoUsers'])->name('settings.zkteco.users');
        Route::get('/settings/zkteco/commands', [SettingController::class, 'zktecoCommandsIndex'])->name('settings.zkteco.commands.index');
        Route::get('/settings/email/mail-logs', [SettingController::class, 'emailMailLogs'])->name('settings.email.mail-logs');
        Route::get('/settings/{section}', [SettingController::class, 'edit'])->name('settings.edit');
    });
    Route::middleware('permission:settings.update')->group(function () {
        Route::put('/settings/{section}', [SettingController::class, 'update'])->name('settings.update');
        Route::post('/settings/zkteco/test-connection', [SettingController::class, 'testZktecoConnection'])->name('settings.zkteco.test-connection');
        Route::delete('/settings/zkteco/users/{uid}', [SettingController::class, 'zktecoDeleteUser'])->name('settings.zkteco.users.destroy');
        Route::post('/settings/zkteco/regenerate-api-key', [SettingController::class, 'regenerateZktecoApiKey'])->name('settings.zkteco.regenerate-api-key');
        Route::post('/settings/zkteco/commands', [SettingController::class, 'zktecoCommandsStore'])->name('settings.zkteco.commands.store');
    });

    // Gallery
    Route::middleware('permission:settings.view')->group(function () {
        Route::get('/gallery', [GalleryController::class, 'index'])->name('gallery.index');
    });
    Route::middleware('permission:settings.update')->group(function () {
        Route::post('/gallery', [GalleryController::class, 'store'])->name('gallery.store');
        Route::post('/gallery/{galleryImage}/toggle', [GalleryController::class, 'toggle'])->name('gallery.toggle');
        Route::delete('/gallery/{galleryImage}', [GalleryController::class, 'destroy'])->name('gallery.destroy');
    });

    // Contact Messages
    Route::middleware('permission:settings.view')->group(function () {
        Route::get('/contact-messages', [ContactMessageController::class, 'index'])->name('contact-messages.index');
        Route::get('/contact-messages/{contactMessage}', [ContactMessageController::class, 'show'])->name('contact-messages.show');
    });
    Route::middleware('permission:settings.update')->group(function () {
        Route::delete('/contact-messages/{contactMessage}', [ContactMessageController::class, 'destroy'])->name('contact-messages.destroy');
    });
});

// Member Portal
Route::middleware(['member.auth:member'])->prefix('member')->name('member.')->group(function () {
    Route::get('/dashboard', [MemberDashboardController::class, 'index'])->name('dashboard');

    Route::get('/renew', [OnlineRenewalController::class, 'show'])->name('renew');
    Route::post('/renew/initiate', [OnlineRenewalController::class, 'initiate'])->name('renew.initiate');
    Route::get('/renew/checkout/{token}', [OnlineRenewalController::class, 'checkout'])->name('renew.checkout');
    Route::post('/renew/checkout/{token}/confirm', [OnlineRenewalController::class, 'confirm'])->name('renew.confirm');
    Route::get('/renew/success/{payment}', [OnlineRenewalController::class, 'success'])->name('renew.success');

    Route::get('/payments', [MemberPaymentHistoryController::class, 'index'])->name('payments.index');
    Route::get('/payments/{payment}/receipt', [MemberPaymentHistoryController::class, 'receipt'])->name('payments.receipt');

    Route::get('/attendance', [MemberAttendanceHistoryController::class, 'index'])->name('attendance.index');

    Route::get('/profile', [MemberProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [MemberProfileController::class, 'update'])->name('profile.update');
});
