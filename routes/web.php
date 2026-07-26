<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\CrewBankAccountController;
use App\Http\Controllers\CrewCourseController;
use App\Http\Controllers\CrewMaritimeEducationController;
use App\Http\Controllers\CrewAcademicController;
use App\Http\Controllers\CrewDocumentController;
use App\Http\Controllers\CrewNoteController;
use App\Http\Controllers\CrewOffenceController;
use App\Http\Controllers\CrewProfileController;
use App\Http\Controllers\CrewSeaServiceController;
use App\Http\Controllers\CvExportController;
use App\Http\Controllers\AccountController;
use App\Http\Controllers\AccountingReportController;
use App\Http\Controllers\VoucherController;
use App\Http\Controllers\CompanyLicenseController;
use App\Http\Controllers\DocumentDashboardController;
use App\Http\Controllers\BusinessDocumentController;
use App\Http\Controllers\PartnerPayoutController;
use App\Http\Controllers\StaffController;
use App\Http\Controllers\StaffSalaryController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\ListsController;
use App\Http\Controllers\CrewSalaryController;
use App\Http\Controllers\SalaryHoldController;
use App\Http\Controllers\SalarySheetController;
use App\Http\Controllers\CandidateController;
use App\Http\Controllers\RequisitionController;
use App\Http\Controllers\PlacementController;
use App\Http\Controllers\PrincipalContactController;
use App\Http\Controllers\PrincipalController;
use App\Http\Controllers\PrincipalDocumentController;
use App\Http\Controllers\PrincipalVesselController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\IntakeController;
use App\Http\Controllers\NotificationController;
use Illuminate\Support\Facades\Route;

// ---- Guest ----
Route::middleware('guest')->group(function () {
    Route::get('login', [LoginController::class, 'show'])->name('login');
    Route::post('login', [LoginController::class, 'login']);
});
Route::post('logout', [LoginController::class, 'logout'])->name('logout')->middleware('auth');

// Public — website career page (goldencareerbd.com/career) posts CVs here.
Route::post('career/submit', [IntakeController::class, 'publicSubmit'])->name('career.submit');

// ---- Authenticated + active ----
Route::middleware(['auth', 'active'])->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    // Module 1 — Crew Management
    Route::get('crew', [CrewProfileController::class, 'index'])->middleware('permission:crew.view')->name('crew.index');
    Route::get('crew/create', [CrewProfileController::class, 'create'])->middleware('permission:crew.create')->name('crew.create');
    Route::post('crew/parse-cv', [CrewProfileController::class, 'parseCv'])->middleware('permission:crew.create')->name('crew.parsecv');
    Route::post('crew', [CrewProfileController::class, 'store'])->middleware('permission:crew.create')->name('crew.store');
    Route::get('crew/{crew}', [CrewProfileController::class, 'show'])->middleware('permission:crew.view')->name('crew.show');
    Route::get('crew/{crew}/edit-profile', [CrewProfileController::class, 'editProfile'])->middleware('permission:crew.edit')->name('crew.editprofile');
    Route::get('crew/{crew}/edit', [CrewProfileController::class, 'edit'])->middleware('permission:crew.edit')->name('crew.edit');
    Route::put('crew/{crew}', [CrewProfileController::class, 'update'])->middleware('permission:crew.edit')->name('crew.update');
    // Deleting a crew profile is Super-Admin-only and requires the admin's own password.
    // Deleted profiles are soft-deleted (retained) and restorable from the Recycle Bin.
    Route::get('crew-trash', [CrewProfileController::class, 'trash'])->middleware('role:Super Admin')->name('crew.trash');
    Route::post('crew-trash/{id}/restore', [CrewProfileController::class, 'restore'])->middleware('role:Super Admin')->name('crew.restore');
    Route::delete('crew/{crew}', [CrewProfileController::class, 'destroy'])->middleware('role:Super Admin')->name('crew.destroy');
    Route::post('crew/{crew}/availability', [CrewProfileController::class, 'toggleAvailability'])->middleware('permission:crew.edit')->name('crew.availability');
    Route::post('crew/{crew}/status', [CrewProfileController::class, 'updateStatus'])->middleware('permission:crew.edit')->name('crew.status.update');
    // Sign a crew off a voyage from anywhere it is reachable (crew profile, salary…).
    Route::post('placements/{placement}/sign-off', [PlacementController::class, 'signOffPlacement'])->middleware('permission:crew.edit')->name('placements.signoff');
    Route::get('crew-export', [CrewProfileController::class, 'export'])->middleware('permission:crew.view')->name('crew.export');
    Route::post('crew/{crew}/upload-cv', [CrewProfileController::class, 'uploadCv'])->middleware('permission:crew.edit')->name('crew.cv.upload');

    // CV export
    Route::get('crew/{crew}/cv.pdf', [CvExportController::class, 'pdf'])->middleware('permission:crew.view')->name('crew.cv.pdf');
    Route::get('crew/{crew}/cv.xlsx', [CvExportController::class, 'excel'])->middleware('permission:crew.view')->name('crew.cv.excel');

    // Child records (permission:crew.edit enforced in the group)
    Route::middleware('permission:crew.edit')->group(function () {
        Route::post('crew/{crew}/documents', [CrewDocumentController::class, 'store'])->name('crew.documents.store');
        Route::put('crew/{crew}/documents/{document}', [CrewDocumentController::class, 'update'])->name('crew.documents.update');
        Route::delete('crew/{crew}/documents/{document}', [CrewDocumentController::class, 'destroy'])->name('crew.documents.destroy');

        Route::post('crew/{crew}/courses', [CrewCourseController::class, 'store'])->name('crew.courses.store');
        Route::delete('crew/{crew}/courses/{course}', [CrewCourseController::class, 'destroy'])->name('crew.courses.destroy');

        Route::post('crew/{crew}/sea-services', [CrewSeaServiceController::class, 'store'])->name('crew.sea.store');
        Route::delete('crew/{crew}/sea-services/{seaService}', [CrewSeaServiceController::class, 'destroy'])->name('crew.sea.destroy');

        // CV template extras: Complete-Profile details, maritime education, academics.
        Route::put('crew/{crew}/details', [CrewProfileController::class, 'updateDetails'])->name('crew.details.update');
        Route::post('crew/{crew}/maritime-education', [CrewMaritimeEducationController::class, 'store'])->name('crew.maritime.store');
        Route::delete('crew/{crew}/maritime-education/{education}', [CrewMaritimeEducationController::class, 'destroy'])->name('crew.maritime.destroy');
        Route::post('crew/{crew}/academics', [CrewAcademicController::class, 'store'])->name('crew.academics.store');
        Route::delete('crew/{crew}/academics/{academic}', [CrewAcademicController::class, 'destroy'])->name('crew.academics.destroy');

        Route::post('crew/{crew}/offences', [CrewOffenceController::class, 'store'])->name('crew.offences.store');
        Route::delete('crew/{crew}/offences/{offence}', [CrewOffenceController::class, 'destroy'])->name('crew.offences.destroy');

        Route::post('crew/{crew}/notes', [CrewNoteController::class, 'store'])->name('crew.notes.store');
        Route::delete('crew/{crew}/notes/{note}', [CrewNoteController::class, 'destroy'])->name('crew.notes.destroy');

        Route::post('crew/{crew}/bank-accounts', [CrewBankAccountController::class, 'store'])->name('crew.bank.store');
        Route::delete('crew/{crew}/bank-accounts/{bankAccount}', [CrewBankAccountController::class, 'destroy'])->name('crew.bank.destroy');
    });

    // Module 3 — Principal Management
    Route::get('principals/directory.pdf', [PrincipalController::class, 'directoryPdf'])->middleware('permission:principal.view')->name('principal.directory');
    Route::get('principals', [PrincipalController::class, 'index'])->middleware('permission:principal.view')->name('principal.index');
    Route::get('principals/create', [PrincipalController::class, 'create'])->middleware('permission:principal.create')->name('principal.create');
    Route::post('principals', [PrincipalController::class, 'store'])->middleware('permission:principal.create')->name('principal.store');
    Route::get('principals/{principal}', [PrincipalController::class, 'show'])->middleware('permission:principal.view')->name('principal.show');
    Route::get('principals/{principal}/crew-export', [PrincipalController::class, 'crewExport'])->middleware('permission:principal.view')->name('principal.crewexport');
    Route::get('principals/{principal}/edit-profile', [PrincipalController::class, 'editProfile'])->middleware('permission:principal.edit')->name('principal.editprofile');
    Route::get('principals/{principal}/edit', [PrincipalController::class, 'edit'])->middleware('permission:principal.edit')->name('principal.edit');
    Route::put('principals/{principal}', [PrincipalController::class, 'update'])->middleware('permission:principal.edit')->name('principal.update');
    Route::delete('principals/{principal}', [PrincipalController::class, 'destroy'])->middleware('permission:principal.delete')->name('principal.destroy');
    Route::post('principals/{principal}/activate', [PrincipalController::class, 'activate'])->middleware('permission:principal.edit')->name('principal.activate');
    Route::post('principals/{principal}/assign-staff', [PrincipalController::class, 'assignStaff'])->middleware('permission:principal.edit')->name('principal.assign');
    Route::delete('principals/{principal}/staff/{assignment}', [PrincipalController::class, 'removeStaff'])->middleware('permission:principal.edit')->name('principal.staff.remove');

    Route::middleware('permission:principal.edit')->group(function () {
        Route::post('principals/{principal}/contacts', [PrincipalContactController::class, 'store'])->name('principal.contacts.store');
        Route::delete('principals/{principal}/contacts/{contact}', [PrincipalContactController::class, 'destroy'])->name('principal.contacts.destroy');
        Route::post('principals/{principal}/vessels', [PrincipalVesselController::class, 'store'])->name('principal.vessels.store');
        Route::delete('principals/{principal}/vessels/{vessel}', [PrincipalVesselController::class, 'destroy'])->name('principal.vessels.destroy');
        Route::post('principals/{principal}/documents', [PrincipalDocumentController::class, 'store'])->name('principal.documents.store');
        Route::delete('principals/{principal}/documents/{document}', [PrincipalDocumentController::class, 'destroy'])->name('principal.documents.destroy');
        Route::post('principals/{principal}/notes', [\App\Http\Controllers\PrincipalNoteController::class, 'store'])->name('principal.notes.store');
        Route::delete('principals/{principal}/notes/{note}', [\App\Http\Controllers\PrincipalNoteController::class, 'destroy'])->name('principal.notes.destroy');
        Route::post('principals/{principal}/offences', [\App\Http\Controllers\PrincipalOffenceController::class, 'store'])->name('principal.offences.store');
        Route::delete('principals/{principal}/offences/{offence}', [\App\Http\Controllers\PrincipalOffenceController::class, 'destroy'])->name('principal.offences.destroy');
        Route::post('principals/{principal}/placements', [PlacementController::class, 'store'])->name('principal.placements.store');
        Route::post('principals/{principal}/placements/{placement}/sign-off', [PlacementController::class, 'signOff'])->name('principal.placements.signoff');
        Route::delete('principals/{principal}/placements/{placement}', [PlacementController::class, 'destroy'])->name('principal.placements.destroy');
    });


    // Module 2 — Crew Selection
    Route::get('selection', [RequisitionController::class, 'index'])->middleware('permission:selection.view')->name('selection.index');
    Route::get('selection/create', [RequisitionController::class, 'create'])->middleware('permission:selection.create')->name('selection.create');
    Route::post('selection', [RequisitionController::class, 'store'])->middleware('permission:selection.create')->name('selection.store');
    Route::get('selection/{requisition}', [RequisitionController::class, 'show'])->middleware('permission:selection.view')->name('selection.show');
    Route::get('selection/{requisition}/export', [RequisitionController::class, 'export'])->middleware('permission:selection.view')->name('selection.export');
    Route::get('candidates/{candidate}/checklist.pdf', [CandidateController::class, 'checklistPdf'])->middleware('permission:selection.view')->name('selection.checklist.pdf');
    Route::get('candidates/{candidate}/signon-letter.pdf', [CandidateController::class, 'signOnLetter'])->middleware('permission:selection.view')->name('selection.signon.letter');
    Route::get('selection/{requisition}/edit', [RequisitionController::class, 'edit'])->middleware('permission:selection.edit')->name('selection.edit');
    Route::put('selection/{requisition}', [RequisitionController::class, 'update'])->middleware('permission:selection.edit')->name('selection.update');
    Route::delete('selection/{requisition}', [RequisitionController::class, 'destroy'])->middleware('permission:selection.delete')->name('selection.destroy');

    Route::middleware('permission:selection.edit')->group(function () {
        Route::post('selection/{requisition}/close', [RequisitionController::class, 'close'])->name('selection.close');
        Route::post('selection/{requisition}/staff', [RequisitionController::class, 'addStaff'])->name('selection.staff.store');
        Route::delete('selection/{requisition}/staff/{user}', [RequisitionController::class, 'removeStaff'])->name('selection.staff.destroy');
        Route::post('selection/{requisition}/positions', [RequisitionController::class, 'storePosition'])->name('selection.positions.store');
        Route::delete('selection/{requisition}/positions/{position}', [RequisitionController::class, 'destroyPosition'])->name('selection.positions.destroy');
        Route::post('selection/{requisition}/positions/{position}/unfulfilled', [RequisitionController::class, 'markUnfulfilled'])->name('selection.positions.unfulfilled');

        Route::post('positions/{position}/candidates', [CandidateController::class, 'store'])->name('selection.candidates.store');
        Route::post('candidates/{candidate}/stage', [CandidateController::class, 'stage'])->name('selection.candidates.stage');
        Route::post('candidates/{candidate}/forward', [CandidateController::class, 'forward'])->name('selection.candidates.forward');
        Route::post('candidates/{candidate}/interview', [CandidateController::class, 'interview'])->name('selection.candidates.interview');
        Route::post('candidates/{candidate}/service-charge', [CandidateController::class, 'serviceCharge'])->name('selection.candidates.charge');
        Route::post('candidates/{candidate}/sign-on', [CandidateController::class, 'signOn'])->name('selection.candidates.signon');
        Route::post('candidates/{candidate}/sign-off', [CandidateController::class, 'signOffCandidate'])->name('selection.candidates.signoff');
        Route::delete('candidates/{candidate}', [CandidateController::class, 'destroy'])->name('selection.candidates.destroy');
        Route::post('candidates/{candidate}/checklist', [CandidateController::class, 'addChecklistItem'])->name('selection.checklist.store');
        Route::post('candidates/{candidate}/checklist/remap', [CandidateController::class, 'remapChecklist'])->name('selection.checklist.remap');
        Route::post('checklist/{item}/status', [CandidateController::class, 'setChecklistStatus'])->name('selection.checklist.status');
        Route::post('checklist/{item}/remark', [CandidateController::class, 'remarkChecklistItem'])->name('selection.checklist.remark');
        Route::delete('checklist/{item}', [CandidateController::class, 'destroyChecklistItem'])->name('selection.checklist.destroy');
    });


    // Module 4 — Crew Salary Management
    Route::get('salary', [SalarySheetController::class, 'index'])->middleware('permission:salary.view')->name('salary.index');
    Route::get('salary/create', [SalarySheetController::class, 'create'])->middleware('permission:salary.create')->name('salary.create');
    Route::post('salary', [SalarySheetController::class, 'store'])->middleware('permission:salary.create')->name('salary.store');
    Route::get('salary/{salary}', [SalarySheetController::class, 'show'])->middleware('permission:salary.view')->name('salary.show');
    Route::get('salary/{salary}/pdf', [SalarySheetController::class, 'pdf'])->middleware('permission:salary.view')->name('salary.pdf');
    Route::get('salary/{salary}/excel', [SalarySheetController::class, 'excel'])->middleware('permission:salary.view')->name('salary.excel');
    Route::get('crew/{crew}/salary', [CrewSalaryController::class, 'index'])->middleware('permission:salary.view')->name('crew.salary');

    Route::middleware('permission:salary.edit')->group(function () {
        Route::put('salary/{salary}/lines/{line}', [SalarySheetController::class, 'updateLine'])->name('salary.lines.update');
        Route::post('salary/{salary}/lines', [SalarySheetController::class, 'addLine'])->name('salary.lines.store');
        Route::delete('salary/{salary}/lines/{line}', [SalarySheetController::class, 'removeLine'])->name('salary.lines.destroy');
        Route::post('salary/{salary}/reconcile', [SalarySheetController::class, 'reconcile'])->name('salary.reconcile');
        Route::post('salary/{salary}/company-sheet', [SalarySheetController::class, 'uploadCompanySheet'])->name('salary.company_sheet');
        Route::delete('salary/{salary}', [SalarySheetController::class, 'destroy'])->name('salary.destroy');
        Route::post('salary/{salary}/lines/{line}/hold', [SalaryHoldController::class, 'hold'])->name('salary.hold');
        Route::post('salary/{salary}/lines/{line}/release', [SalaryHoldController::class, 'release'])->name('salary.release');
    });

    // Approval requires the dedicated permission (Super Admin).
    Route::post('salary/{salary}/approve', [SalarySheetController::class, 'approve'])->middleware('permission:salary.approve')->name('salary.approve');


    // Module 5 — Document Management (dashboard; crew documents live in Module 1)
    Route::get('documents', [DocumentDashboardController::class, 'index'])->middleware('permission:document.view')->name('document.index');
    Route::get('documents/sign-on-register', [DocumentDashboardController::class, 'signOnRegister'])->middleware('permission:document.view')->name('document.signon.register');
    Route::get('documents/{document}/history', [DocumentDashboardController::class, 'history'])->middleware('permission:document.view')->name('document.history');
    Route::get('documents/export', [DocumentDashboardController::class, 'export'])->middleware('permission:document.view')->name('document.export');
    Route::get('business-documents', [BusinessDocumentController::class, 'index'])->middleware('permission:document.view')->name('document.business.index');
    Route::get('business-documents/create', [BusinessDocumentController::class, 'create'])->middleware('permission:document.create')->name('document.business.create');
    Route::post('business-documents', [BusinessDocumentController::class, 'store'])->middleware('permission:document.create')->name('document.business.store');
    Route::get('business-documents/{document}/edit', [BusinessDocumentController::class, 'edit'])->middleware('permission:document.edit')->name('document.business.edit');
    Route::put('business-documents/{document}', [BusinessDocumentController::class, 'update'])->middleware('permission:document.edit')->name('document.business.update');
    Route::delete('business-documents/{document}', [BusinessDocumentController::class, 'destroy'])->middleware('permission:document.delete')->name('document.business.destroy');

    // Module 7 — Company Licence Management
    Route::get('licenses', [CompanyLicenseController::class, 'index'])->middleware('permission:license.view')->name('license.index');
    Route::get('licenses/export', [CompanyLicenseController::class, 'export'])->middleware('permission:license.view')->name('license.export');
    Route::get('licenses/{license}/history', [CompanyLicenseController::class, 'history'])->middleware('permission:license.view')->name('license.history');
    Route::get('licenses/create', [CompanyLicenseController::class, 'create'])->middleware('permission:license.create')->name('license.create');
    Route::post('licenses', [CompanyLicenseController::class, 'store'])->middleware('permission:license.create')->name('license.store');
    Route::get('licenses/{license}/edit', [CompanyLicenseController::class, 'edit'])->middleware('permission:license.edit')->name('license.edit');
    Route::put('licenses/{license}', [CompanyLicenseController::class, 'update'])->middleware('permission:license.edit')->name('license.update');
    Route::delete('licenses/{license}', [CompanyLicenseController::class, 'destroy'])->middleware('permission:license.delete')->name('license.destroy');

    // Module 6 — Staff & Partner Management
    Route::get('staff', [StaffController::class, 'index'])->middleware('permission:staff.view')->name('staff.index');
    Route::get('staff/create', [StaffController::class, 'create'])->middleware('permission:staff.create')->name('staff.create');
    Route::post('staff', [StaffController::class, 'store'])->middleware('permission:staff.create')->name('staff.store');
    Route::get('staff/{staff}', [StaffController::class, 'show'])->middleware('permission:staff.view')->name('staff.show');
    Route::get('staff/{staff}/edit', [StaffController::class, 'edit'])->middleware('permission:staff.edit')->name('staff.edit');
    Route::put('staff/{staff}', [StaffController::class, 'update'])->middleware('permission:staff.edit')->name('staff.update');
    Route::middleware('permission:staff.edit')->group(function () {
        Route::post('staff/{staff}/payouts', [PartnerPayoutController::class, 'store'])->name('staff.payouts.store');
        Route::post('staff/{staff}/payouts/{payout}/paid', [PartnerPayoutController::class, 'markPaid'])->name('staff.payouts.paid');
        Route::delete('staff/{staff}/payouts/{payout}', [PartnerPayoutController::class, 'destroy'])->name('staff.payouts.destroy');
    });


    // Module 8 — Accounting (double-entry)
    Route::get('accounting', [AccountingReportController::class, 'dashboard'])->middleware('permission:accounting.view')->name('accounting.dashboard');

    // Chart of accounts
    Route::get('accounting/accounts', [AccountController::class, 'index'])->middleware('permission:accounting.view')->name('accounting.accounts.index');
    Route::get('accounting/accounts/create', [AccountController::class, 'create'])->middleware('permission:accounting.create')->name('accounting.accounts.create');
    Route::post('accounting/accounts', [AccountController::class, 'store'])->middleware('permission:accounting.create')->name('accounting.accounts.store');
    Route::get('accounting/accounts/{account}/edit', [AccountController::class, 'edit'])->middleware('permission:accounting.edit')->name('accounting.accounts.edit');
    Route::put('accounting/accounts/{account}', [AccountController::class, 'update'])->middleware('permission:accounting.edit')->name('accounting.accounts.update');

    // Vouchers
    Route::get('accounting/vouchers', [VoucherController::class, 'index'])->middleware('permission:accounting.view')->name('accounting.vouchers.index');
    Route::get('accounting/vouchers/create', [VoucherController::class, 'create'])->middleware('permission:accounting.create')->name('accounting.vouchers.create');
    Route::post('accounting/vouchers', [VoucherController::class, 'store'])->middleware('permission:accounting.create')->name('accounting.vouchers.store');
    Route::get('accounting/vouchers/{voucher}', [VoucherController::class, 'show'])->middleware('permission:accounting.view')->name('accounting.vouchers.show');
    Route::post('accounting/vouchers/{voucher}/void', [VoucherController::class, 'void'])->middleware('permission:accounting.post')->name('accounting.vouchers.void');

    // Reports
    Route::get('accounting/reports/trial-balance', [AccountingReportController::class, 'trialBalance'])->middleware('permission:accounting.view')->name('accounting.reports.trial');
    Route::get('accounting/reports/ledger', [AccountingReportController::class, 'ledger'])->middleware('permission:accounting.view')->name('accounting.reports.ledger');
    Route::get('accounting/reports/day-book', [AccountingReportController::class, 'dayBook'])->middleware('permission:accounting.view')->name('accounting.reports.daybook');
    Route::get('accounting/reports/cash-bank', [AccountingReportController::class, 'cashBank'])->middleware('permission:accounting.view')->name('accounting.reports.cashbank');
    Route::get('accounting/reports/profit-loss', [AccountingReportController::class, 'profitLoss'])->middleware('permission:accounting.view')->name('accounting.reports.pnl');
    Route::get('accounting/reports/balance-sheet', [AccountingReportController::class, 'balanceSheet'])->middleware('permission:accounting.view')->name('accounting.reports.balancesheet');
    Route::get('accounting/reports/receivables', [AccountingReportController::class, 'receivables'])->middleware('permission:accounting.view')->name('accounting.reports.receivables');
    Route::get('accounting/reports/payables', [AccountingReportController::class, 'payables'])->middleware('permission:accounting.view')->name('accounting.reports.payables');
    Route::get('accounting/reports/party-ledger', [AccountingReportController::class, 'partyLedger'])->middleware('permission:accounting.view')->name('accounting.reports.party');
    Route::get('accounting/reports/cash-flow', [AccountingReportController::class, 'cashFlow'])->middleware('permission:accounting.view')->name('accounting.reports.cashflow');
    Route::get('accounting/reports/tax', [AccountingReportController::class, 'taxReport'])->middleware('permission:accounting.view')->name('accounting.reports.tax');
    Route::post('accounting/close-books', [AccountingReportController::class, 'closeBooks'])->middleware('permission:accounting.post')->name('accounting.close');


    // Notifications (in-app panel) — available to all authenticated users
    Route::get('notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::post('notifications/read-all', [NotificationController::class, 'readAll'])->name('notifications.readAll');
    Route::post('notifications/{notification}/read', [NotificationController::class, 'read'])->name('notifications.read');
    Route::post('notifications/{notification}/unread', [NotificationController::class, 'unread'])->name('notifications.unread');
    Route::post('notifications/{notification}/open', [NotificationController::class, 'open'])->name('notifications.open');


    // Module 6 — staff payroll
    Route::get('staff-payroll', [StaffSalaryController::class, 'index'])->middleware('permission:staff.view')->name('staff.payroll.index');
    Route::post('staff-payroll/generate', [StaffSalaryController::class, 'generate'])->middleware('permission:staff.edit')->name('staff.payroll.generate');
    Route::put('staff-payroll/{salary}', [StaffSalaryController::class, 'update'])->middleware('permission:staff.edit')->name('staff.payroll.update');
    Route::post('staff-payroll/{salary}/pay', [StaffSalaryController::class, 'pay'])->middleware('permission:staff.edit')->name('staff.payroll.pay');

    // Settings — roles & permissions matrix
    Route::get('settings/roles', [RoleController::class, 'index'])->middleware('permission:settings.view')->name('settings.roles');
    Route::put('settings/roles/{role}', [RoleController::class, 'update'])->middleware('permission:settings.edit')->name('settings.roles.update');

    // Settings → customisable lists (ranks, designations)
    Route::get('settings/lists', [ListsController::class, 'index'])->middleware('permission:settings.view')->name('settings.lists');
    Route::middleware('permission:settings.edit')->group(function () {
        Route::post('settings/ranks', [ListsController::class, 'storeRank'])->name('settings.ranks.store');
        Route::put('settings/ranks/{rank}', [ListsController::class, 'updateRank'])->name('settings.ranks.update');
        Route::post('settings/ranks/{rank}/toggle', [ListsController::class, 'toggleRank'])->name('settings.ranks.toggle');
        Route::post('settings/designations', [ListsController::class, 'storeDesignation'])->name('settings.designations.store');
        Route::put('settings/designations/{designation}', [ListsController::class, 'updateDesignation'])->name('settings.designations.update');
        Route::post('settings/designations/{designation}/toggle', [ListsController::class, 'toggleDesignation'])->name('settings.designations.toggle');
        Route::post('settings/academies', [ListsController::class, 'storeAcademy'])->name('settings.academies.store');
        Route::put('settings/academies/{academy}', [ListsController::class, 'updateAcademy'])->name('settings.academies.update');
        Route::post('settings/academies/{academy}/toggle', [ListsController::class, 'toggleAcademy'])->name('settings.academies.toggle');
        Route::post('settings/departments', [ListsController::class, 'storeDepartment'])->name('settings.departments.store');
        Route::put('settings/departments/{department}', [ListsController::class, 'updateDepartment'])->name('settings.departments.update');
        Route::post('settings/departments/{department}/toggle', [ListsController::class, 'toggleDepartment'])->name('settings.departments.toggle');
        Route::post('settings/vessel-types', [ListsController::class, 'storeVesselType'])->name('settings.vesseltypes.store');
        Route::put('settings/vessel-types/{vesselType}', [ListsController::class, 'updateVesselType'])->name('settings.vesseltypes.update');
        Route::post('settings/vessel-types/{vesselType}/toggle', [ListsController::class, 'toggleVesselType'])->name('settings.vesseltypes.toggle');
        Route::post('settings/checklist', [ListsController::class, 'storeChecklistItem'])->name('settings.checklist.store');
        Route::put('settings/checklist/{checklistItem}', [ListsController::class, 'updateChecklistItem'])->name('settings.checklist.update');
        Route::post('settings/checklist/{checklistItem}/toggle', [ListsController::class, 'toggleChecklistItem'])->name('settings.checklist.toggle');
        Route::delete('settings/checklist/{checklistItem}', [ListsController::class, 'destroyChecklistItem'])->name('settings.checklist.destroy');
        Route::post('settings/signoff-reasons', [ListsController::class, 'storeSignOffReason'])->name('settings.signoffreasons.store');
        Route::put('settings/signoff-reasons/{signOffReason}', [ListsController::class, 'updateSignOffReason'])->name('settings.signoffreasons.update');
        Route::post('settings/signoff-reasons/{signOffReason}/toggle', [ListsController::class, 'toggleSignOffReason'])->name('settings.signoffreasons.toggle');
        Route::delete('settings/signoff-reasons/{signOffReason}', [ListsController::class, 'destroySignOffReason'])->name('settings.signoffreasons.destroy');
    });


    // CV Intake + Edit Approvals
    Route::get('intake', [IntakeController::class, 'index'])->middleware('permission:crew.view')->name('intake.index');
    Route::post('intake/submissions', [IntakeController::class, 'store'])->middleware('permission:crew.create')->name('intake.store');
    Route::get('intake/submissions/{submission}/review', [IntakeController::class, 'reviewCv'])->middleware('permission:crew.view')->name('intake.review');
    Route::post('intake/submissions/{submission}/approve', [IntakeController::class, 'approveCv'])->middleware('permission:crew.create')->name('intake.approve');
    Route::post('intake/submissions/{submission}/reject', [IntakeController::class, 'rejectCv'])->middleware('permission:crew.create')->name('intake.reject');
    Route::post('intake/changes/{change}/approve', [IntakeController::class, 'approveChange'])->middleware('permission:crew.edit')->name('intake.changes.approve');
    Route::post('intake/changes/{change}/reject', [IntakeController::class, 'rejectChange'])->middleware('permission:crew.edit')->name('intake.changes.reject');
});