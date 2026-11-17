<?php
$pageTitle = "Company Profile";
require_once __DIR__ . '/header.php';

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $profileData = [
        'name'        => trim($_POST['name'] ?? ''),
        'company'     => trim($_POST['company'] ?? ''),
        'phone'       => trim($_POST['phone'] ?? ''),
        'country'     => trim($_POST['country'] ?? 'India'),
        'state'       => trim($_POST['state'] ?? ''),
        'city'        => trim($_POST['city'] ?? ''),
        'address'     => trim($_POST['address'] ?? ''),
        'website'     => trim($_POST['website'] ?? ''),
        'description' => trim($_POST['description'] ?? '')
    ];

    // Handle Company Logo Upload if provided
    if (isset($_FILES['logo']) && !empty($_FILES['logo']['name'])) {
        $logoDir = __DIR__ . '/../assets/images/uploads/';
        if (!is_dir($logoDir)) @mkdir($logoDir, 0777, true);
        
        $ext = strtolower(pathinfo($_FILES['logo']['name'], PATHINFO_EXTENSION));
        if (in_array($ext, ['jpg', 'jpeg', 'png', 'webp'])) {
            $logoName = 'logo_' . $userId . '_' . time() . '.' . $ext;
            if (move_uploaded_file($_FILES['logo']['tmp_name'], $logoDir . $logoName)) {
                $profileData['logo'] = 'assets/images/uploads/' . $logoName;
            }
        }
    }

    $res = updateUserProfile($userId, $profileData);
    if ($res['success']) {
        $message = "Company profile details updated successfully!";
        // Refresh session
        $currentUser = getCurrentUser();
    } else {
        $error = $res['message'];
    }
}
?>

<!-- BREADCRUMB & HEADER -->
<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-2">
  <div>
    <div class="flex items-center gap-2 text-xs text-slate-500 mb-1">
      <a href="index.php" class="hover:text-blue-600">Dashboard</a>
      <span>/</span>
      <span class="text-slate-800 font-bold">Company Profile</span>
    </div>
    <h1 class="text-2xl font-black text-slate-900 tracking-tight">Company &amp; Account Profile</h1>
    <p class="text-xs text-slate-500">Keep your industrial facility information updated for buyer trust and verification.</p>
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

<div class="max-w-4xl bg-white rounded-3xl border border-slate-200 shadow-xs p-6 sm:p-8">
  <form action="profile.php" method="POST" enctype="multipart/form-data" class="space-y-6">
    
    <!-- Row 1: Personal & Company Names -->
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 sm:gap-6">
      <div>
        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
          Full Name <span class="text-red-500">*</span>
        </label>
        <input 
          type="text" 
          name="name" 
          value="<?php echo htmlspecialchars($currentUser['name'] ?? ''); ?>" 
          required 
          class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-slate-800 text-xs font-semibold focus:outline-none focus:border-joule-blue"
        >
      </div>

      <div>
        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
          Company Name <span class="text-red-500">*</span>
        </label>
        <input 
          type="text" 
          name="company" 
          value="<?php echo htmlspecialchars($currentUser['company'] ?? ''); ?>" 
          required 
          class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-slate-800 text-xs font-semibold focus:outline-none focus:border-joule-blue"
        >
      </div>
    </div>

    <!-- Row 2: Email & Phone -->
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 sm:gap-6">
      <div>
        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
          Email Address (Login ID)
        </label>
        <input 
          type="email" 
          value="<?php echo htmlspecialchars($currentUser['email'] ?? ''); ?>" 
          disabled 
          class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-slate-500 bg-slate-50 text-xs font-semibold cursor-not-allowed"
        >
        <p class="text-[10px] text-slate-400 mt-1">To change email, contact security support.</p>
      </div>

      <div>
        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
          Mobile Number <span class="text-red-500">*</span>
        </label>
        <input 
          type="tel" 
          name="phone" 
          value="<?php echo htmlspecialchars($currentUser['phone'] ?? ''); ?>" 
          required 
          class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-slate-800 text-xs font-semibold focus:outline-none focus:border-joule-blue"
        >
      </div>
    </div>

    <!-- Row 3: Country, State, City -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 sm:gap-6">
      <div>
        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
          Country <span class="text-red-500">*</span>
        </label>
        <input 
          type="text" 
          name="country" 
          value="<?php echo htmlspecialchars($currentUser['country'] ?? 'India'); ?>" 
          required 
          class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-slate-800 text-xs font-semibold focus:outline-none focus:border-joule-blue"
        >
      </div>

      <div>
        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
          State / Region
        </label>
        <input 
          type="text" 
          name="state" 
          value="<?php echo htmlspecialchars($currentUser['state'] ?? ''); ?>" 
          placeholder="e.g. Maharashtra, Bavaria" 
          class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-slate-800 text-xs font-semibold focus:outline-none focus:border-joule-blue"
        >
      </div>

      <div>
        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
          City / Industrial Zone <span class="text-red-500">*</span>
        </label>
        <input 
          type="text" 
          name="city" 
          value="<?php echo htmlspecialchars($currentUser['city'] ?? ''); ?>" 
          required 
          class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-slate-800 text-xs font-semibold focus:outline-none focus:border-joule-blue"
        >
      </div>
    </div>

    <!-- Business Address & Website -->
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 sm:gap-6">
      <div>
        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
          Plant / Facility Address
        </label>
        <input 
          type="text" 
          name="address" 
          value="<?php echo htmlspecialchars($currentUser['address'] ?? ''); ?>" 
          placeholder="Plot No., Industrial Area, Phase II" 
          class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-slate-800 text-xs font-semibold focus:outline-none focus:border-joule-blue"
        >
      </div>

      <div>
        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
          Company Website URL
        </label>
        <input 
          type="url" 
          name="website" 
          value="<?php echo htmlspecialchars($currentUser['website'] ?? ''); ?>" 
          placeholder="https://www.yourcompany.com" 
          class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-slate-800 text-xs font-semibold focus:outline-none focus:border-joule-blue"
        >
      </div>
    </div>

    <!-- Company Description -->
    <div>
      <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
        Company Description &amp; Manufacturing Specialty
      </label>
      <textarea 
        name="description" 
        rows="3" 
        placeholder="Brief overview of your manufacturing plant, CNC precision machining capacities, OEM partnerships, or surplus equipment liquidation." 
        class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-slate-800 text-xs font-semibold focus:outline-none focus:border-joule-blue"
      ><?php echo htmlspecialchars($currentUser['description'] ?? ''); ?></textarea>
    </div>

    <!-- Company Logo Upload -->
    <div>
      <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
        Company Logo (Optional)
      </label>
      <input 
        type="file" 
        name="logo" 
        accept="image/*" 
        class="text-xs text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-slate-100 file:text-slate-700 hover:file:bg-slate-200"
      >
    </div>

    <!-- Save Button -->
    <div class="pt-4 border-t border-slate-100 flex items-center justify-between">
      <span class="text-xs text-slate-500">
        Changes will reflect across all your active machinery listings.
      </span>
      <button 
        type="submit" 
        class="bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs py-3 px-6 rounded-xl shadow-md transition-all cursor-pointer"
      >
        Save Profile Changes
      </button>
    </div>

  </form>
</div>

<?php
require_once __DIR__ . '/footer.php';
?>
