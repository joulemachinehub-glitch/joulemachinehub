<?php
$pageTitle = "Change Password";
require_once __DIR__ . '/header.php';

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $currentPass = trim($_POST['current_password'] ?? '');
    $newPass = trim($_POST['new_password'] ?? '');
    $confirmPass = trim($_POST['confirm_password'] ?? '');

    if (empty($currentPass) || empty($newPass) || empty($confirmPass)) {
        $error = 'Please fill in all password fields.';
    } elseif ($newPass !== $confirmPass) {
        $error = 'New password and confirmation do not match.';
    } elseif (strlen($newPass) < 6) {
        $error = 'New password must be at least 6 characters long.';
    } else {
        $res = changeUserPassword($userId, $currentPass, $newPass);
        if ($res['success']) {
            $message = "Your password has been changed successfully!";
        } else {
            $error = $res['message'];
        }
    }
}
?>

<!-- BREADCRUMB & HEADER -->
<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-2">
  <div>
    <div class="flex items-center gap-2 text-xs text-slate-500 mb-1">
      <a href="index.php" class="hover:text-blue-600">Dashboard</a>
      <span>/</span>
      <span class="text-slate-800 font-bold">Change Password</span>
    </div>
    <h1 class="text-2xl font-black text-slate-900 tracking-tight">Update Account Security</h1>
    <p class="text-xs text-slate-500">Ensure your industrial seller account remains protected with a strong password.</p>
  </div>
</div>

<?php if (!empty($message)): ?>
  <div class="p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-semibold flex items-center gap-2.5 animate-in fade-in">
    <i class="fa-solid fa-circle-check text-base text-emerald-600 shrink-0"></i>
    <div><?php echo htmlspecialchars($message); ?></div>
  </div>
<?php endif; ?>

<?php if (!empty($error)): ?>
  <div class="p-4 rounded-xl bg-red-50 border border-red-200 text-red-700 text-xs font-semibold flex items-center gap-2.5">
    <i class="fa-solid fa-circle-exclamation text-base text-red-600 shrink-0"></i>
    <div><?php echo htmlspecialchars($error); ?></div>
  </div>
<?php endif; ?>

<div class="max-w-md bg-white rounded-3xl border border-slate-200 shadow-xs p-6 sm:p-8">
  <form action="change-password.php" method="POST" class="space-y-4">
    
    <div>
      <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
        Current Password <span class="text-red-500">*</span>
      </label>
      <input 
        type="password" 
        name="current_password" 
        required 
        placeholder="Enter current password" 
        class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-slate-800 text-xs font-semibold focus:outline-none focus:border-joule-blue"
      >
    </div>

    <div>
      <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
        New Password <span class="text-red-500">*</span>
      </label>
      <input 
        type="password" 
        name="new_password" 
        required 
        placeholder="Minimum 6 characters" 
        class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-slate-800 text-xs font-semibold focus:outline-none focus:border-joule-blue"
      >
    </div>

    <div>
      <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
        Confirm New Password <span class="text-red-500">*</span>
      </label>
      <input 
        type="password" 
        name="confirm_password" 
        required 
        placeholder="Re-enter new password" 
        class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-slate-800 text-xs font-semibold focus:outline-none focus:border-joule-blue"
      >
    </div>

    <div class="pt-3">
      <button 
        type="submit" 
        class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs py-3 px-6 rounded-xl shadow-md transition-all cursor-pointer"
      >
        Change Password
      </button>
    </div>

  </form>
</div>

<?php
require_once __DIR__ . '/footer.php';
?>
