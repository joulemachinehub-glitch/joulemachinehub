<?php
$pageTitle = "Add New Machine";
require_once __DIR__ . '/header.php';

$error = '';
$success = '';
$publishedSlug = '';
$publishedRef = '';

// Check listing quota
$canUpload = $userStats['can_upload'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!$canUpload && empty($_POST['is_draft'])) {
        $error = "Listing Quota Reached: Your {$userSub['plan_name']} allows up to {$userSub['listing_limit']} machines. Please upgrade your membership to list additional machinery.";
    } else {
        $submissionStatus = !empty($_POST['is_draft']) ? 'draft' : 'pending';
        
        // Populate seller upload payload
        $uploadData = $_POST;
        $uploadData['status'] = $submissionStatus;
        $uploadData['title'] = trim($_POST['machine_name'] ?? '');
        $uploadData['value_raw'] = ($_POST['currency'] ?? 'INR') . ' ' . number_format((float)($_POST['price'] ?? 0));
        $uploadData['currency_origin'] = $_POST['currency'] ?? 'INR';
        $uploadData['price_usd'] = ($_POST['currency'] === 'USD') ? (float)($_POST['price'] ?? 0) : round((float)($_POST['price'] ?? 0) / 85, 2);
        $uploadData['equipment_type'] = $_POST['subcategory'] ?? ($_POST['category'] ?? 'Industrial Machine');
        $uploadData['condition_state'] = $_POST['condition'] ?? 'Used';
        $uploadData['date_of_mfg'] = $_POST['year'] ?? date('Y');
        $uploadData['plant_address'] = trim(($_POST['city'] ?? '') . ', ' . ($_POST['state'] ?? '') . ', ' . ($_POST['country'] ?? ''));
        $uploadData['hours_run'] = $_POST['hours_run'] ?? 'Plant Maintained';
        $uploadData['power_test'] = 'Yes (Inspection Ready)';
        $uploadData['stroke_length'] = $_POST['stroke_length'] ?? 'Standard';
        $uploadData['speed_spm'] = $_POST['operating_speed'] ?? 'Variable';
        $uploadData['die_height'] = $_POST['voltage'] ?? 'Standard';
        $uploadData['motor_power'] = $_POST['power_rating'] ?? '';
        $uploadData['short_desc'] = trim($_POST['short_desc'] ?? substr($_POST['technical_description'] ?? '', 0, 150));
        $uploadData['detailed_desc'] = trim($_POST['technical_description'] ?? '');
        $uploadData['feature_1'] = $_POST['price_type'] ?? 'Negotiable';
        $uploadData['feature_2'] = $_POST['shipping_terms'] ?? 'Ex-Works';
        $uploadData['feature_3'] = 'Capacity: ' . ($_POST['capacity'] ?? 'Industrial');
        $uploadData['feature_4'] = 'Quantity: ' . ($_POST['quantity'] ?? '1');
        $uploadData['manufacturer'] = trim($_POST['manufacturer'] ?? ($_POST['brand'] ?? ''));
        $uploadData['manufacturer_country'] = trim($_POST['manufacturer_country'] ?? ($_POST['country'] ?? ''));
        $uploadData['seller_company'] = $_POST['seller_company'] ?? ($_POST['company_name'] ?? ($currentUser['company'] ?? ''));
        $uploadData['seller_location'] = trim($_POST['seller_location'] ?? (($_POST['city'] ?? '') . ', ' . ($_POST['country'] ?? '')));
        $uploadData['machine_location'] = trim($_POST['machine_location'] ?? (($_POST['city'] ?? '') . ', ' . ($_POST['country'] ?? '')));
        $uploadData['contact_person'] = $_POST['contact_person'] ?? ($currentUser['name'] ?? '');
        $uploadData['seller_phone'] = $_POST['mobile_number'] ?? ($currentUser['phone'] ?? '');
        $uploadData['seller_email'] = $_POST['business_email'] ?? ($currentUser['email'] ?? '');

        // Handle File Uploads (Images, Video, Nameplate, PDF)
        $result = handleSellerMachineUpload($uploadData, $_FILES, $currentUser);
        if ($result['success']) {
            $publishedSlug = $result['product']['slug'] ?? '';
            $publishedRef = $result['product']['product_ref'] ?? '';
            $success = "Your machine has been successfully added to Joule Machine Hub and is now live across the public website!";
            // Refresh stats
            $userStats = getUserListingStats($userId);
            $canUpload = $userStats['can_upload'];
        } else {
            $error = $result['message'];
        }
    }
}
?>

<!-- BREADCRUMB & HEADER -->
<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-2">
  <div>
    <div class="flex items-center gap-2 text-xs text-slate-500 mb-1">
      <a href="/dashboard" class="hover:text-blue-600">Dashboard</a>
      <span>/</span>
      <span class="text-slate-800 font-bold">Add New Machine</span>
    </div>
    <h1 class="text-2xl font-black text-slate-900 tracking-tight">Add New Machinery Listing</h1>
    <p class="text-xs text-slate-500">Complete the 5 sections below to publish your equipment to global verified buyers.</p>
  </div>

  <div class="flex items-center gap-2">
    <span class="text-xs text-slate-500">Plan Quota:</span>
    <span class="text-xs font-bold px-2.5 py-1 rounded-full <?php echo $userSub['plan_details']['badge_color']; ?> border">
      <?php echo $userStats['total']; ?> / <?php echo $userStats['listing_limit']; ?> Used
    </span>
  </div>
</div>

<?php if (!empty($success)): ?>
  <div class="p-6 rounded-2xl bg-emerald-50 border-2 border-emerald-400 text-emerald-950 shadow-md flex items-start gap-4 animate-in fade-in">
    <div class="w-12 h-12 rounded-2xl bg-emerald-600 text-white flex items-center justify-center text-xl shrink-0 shadow-md">
      <i class="fa-solid fa-circle-check"></i>
    </div>
    <div class="space-y-2">
      <div class="flex items-center gap-2">
        <span class="text-[10px] font-black uppercase px-2 py-0.5 rounded-md bg-emerald-600 text-white tracking-wider">Live on Marketplace</span>
        <span class="text-xs text-emerald-800 font-bold">Listing Ref: #<?php echo htmlspecialchars($publishedRef); ?></span>
      </div>
      <h3 class="font-black text-base text-emerald-950">Machine Published Directly to Website!</h3>
      <p class="text-xs text-emerald-800 leading-relaxed max-w-xl">
        <?php echo htmlspecialchars($success); ?> Buyers worldwide can now view full technical specifications, inspect photos, and submit RFQ inquiries.
      </p>
      <div class="flex items-center gap-3 pt-2 flex-wrap">
        <?php if (!empty($publishedSlug)): ?>
          <a href="/product/<?php echo htmlspecialchars($publishedSlug); ?>" target="_blank" class="text-xs font-black text-slate-950 bg-gradient-to-r from-amber-400 to-yellow-400 hover:from-amber-500 hover:to-yellow-500 px-4 py-2 rounded-xl shadow-md transition-all inline-flex items-center gap-1.5 border border-amber-300">
            <span>View Live Machine on Website</span>
            <i class="fa-solid fa-arrow-up-right-from-square text-[10px]"></i>
          </a>
        <?php endif; ?>
        <a href="my-machines.php" class="text-xs font-bold text-emerald-950 bg-white hover:bg-emerald-100 px-3.5 py-2 rounded-xl transition-colors border border-emerald-300 inline-flex items-center gap-1.5">
          <i class="fa-solid fa-boxes-stacked text-xs"></i>
          <span>View in My Machines</span>
        </a>
        <a href="add-machine.php" class="text-xs font-bold text-emerald-800 hover:text-emerald-950 px-2 py-2">
          + Upload Another Machine
        </a>
      </div>
    </div>
  </div>
<?php endif; ?>

<?php if (!empty($error)): ?>
  <div class="p-4 rounded-xl bg-red-50 border border-red-200 text-red-700 text-xs font-semibold flex items-center gap-3">
    <i class="fa-solid fa-circle-exclamation text-base shrink-0"></i>
    <div><?php echo htmlspecialchars($error); ?></div>
  </div>
<?php endif; ?>

<?php if (!$canUpload && empty($success)): ?>
  <div class="p-5 rounded-2xl bg-amber-50 border-2 border-amber-300 text-amber-900 shadow-sm flex flex-col sm:flex-row sm:items-center justify-between gap-4">
    <div class="flex items-center gap-3.5">
      <div class="w-10 h-10 rounded-xl bg-amber-500 text-slate-950 flex items-center justify-center text-lg shrink-0 font-black shadow-md">
        <i class="fa-solid fa-crown"></i>
      </div>
      <div>
        <h4 class="font-bold text-sm text-amber-950">Listing Limit Reached (<?php echo $userStats['listing_limit']; ?> Machines)</h4>
        <p class="text-xs text-amber-800 mt-0.5">
          Your current <?php echo htmlspecialchars($userSub['plan_name']); ?> allows up to <?php echo $userStats['listing_limit']; ?> machine listings. Upgrade to list more!
        </p>
      </div>
    </div>
    <a 
      href="membership.php" 
      class="bg-joule-yellow hover:bg-amber-400 text-slate-950 font-black text-xs py-2.5 px-5 rounded-xl shadow-md transition-all shrink-0 inline-flex items-center gap-1.5"
    >
      <i class="fa-solid fa-bolt text-[10px]"></i>
      <span>Upgrade to Basic (10 Machines)</span>
    </a>
  </div>
<?php endif; ?>

<!-- 5-SECTION MACHINE UPLOAD FORM -->
<form action="add-machine.php" method="POST" enctype="multipart/form-data" class="space-y-6">
  
  <!-- ==================== SECTION 1: MACHINE INFORMATION ==================== -->
  <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
    <div class="bg-slate-50 border-b border-slate-200 px-6 py-4 flex items-center justify-between">
      <div class="flex items-center gap-3">
        <span class="w-7 h-7 rounded-lg bg-blue-600 text-white font-bold text-xs flex items-center justify-center">1</span>
        <div>
          <h3 class="font-bold text-sm text-slate-900">Machine Information</h3>
          <p class="text-[11px] text-slate-500">General classification, brand, and manufacturing details</p>
        </div>
      </div>
      <span class="text-[11px] text-slate-400 font-semibold uppercase tracking-wider">Required</span>
    </div>

    <div class="p-6 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-6">
      
      <!-- Machine Name -->
      <div class="sm:col-span-2 lg:col-span-4">
        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
          Machine Name / Title <span class="text-red-500">*</span>
        </label>
        <input 
          type="text" 
          name="machine_name" 
          required 
          placeholder="e.g. Mazak Quick Turn 250-MSY CNC Turning Center" 
          class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-slate-800 text-xs font-semibold focus:outline-none focus:border-joule-blue"
        >
      </div>

      <!-- Category -->
      <div>
        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
          Category <span class="text-red-500">*</span>
        </label>
        <select 
          name="category" 
          required 
          class="w-full px-3 py-2.5 rounded-xl border border-slate-300 text-slate-800 text-xs font-semibold focus:outline-none focus:border-joule-blue bg-white"
        >
          <option value="CNC & Machining Centers" selected>CNC &amp; Machining Centers</option>
          <option value="Lathes & Turning">Lathes &amp; Turning</option>
          <option value="Presses & Stamping">Presses &amp; Stamping</option>
          <option value="Cold Formers & Headers">Cold Formers &amp; Headers</option>
          <option value="Plastic & Injection Moulding">Plastic &amp; Moulding</option>
          <option value="Packaging Machinery">Packaging Machinery</option>
          <option value="Generators & Compressors">Generators &amp; Compressors</option>
          <option value="Lifts & Access Equipment">Lifts &amp; Access Equipment</option>
        </select>
      </div>

      <!-- Subcategory -->
      <div>
        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
          Subcategory
        </label>
        <input 
          type="text" 
          name="subcategory" 
          placeholder="e.g. 5-Axis VMC, Slitter, Extruder" 
          class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-slate-800 text-xs font-semibold focus:outline-none focus:border-joule-blue"
        >
      </div>

      <!-- Brand -->
      <div>
        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
          Brand / Make <span class="text-red-500">*</span>
        </label>
        <input 
          type="text" 
          name="brand" 
          required 
          placeholder="e.g. Haas, Mazak, Amada, Dingli" 
          class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-slate-800 text-xs font-semibold focus:outline-none focus:border-joule-blue"
        >
      </div>

      <!-- Manufacturer (OEM) -->
      <div>
        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
          Manufacturer (OEM)
        </label>
        <input 
          type="text" 
          name="manufacturer" 
          placeholder="e.g. Zhejiang Dingli, Komatsu Ltd." 
          class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-slate-800 text-xs font-semibold focus:outline-none focus:border-joule-blue"
        >
      </div>

      <!-- Manufacturer Country -->
      <div>
        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
          Manufacturer Country
        </label>
        <input 
          type="text" 
          name="manufacturer_country" 
          placeholder="e.g. Japan, Germany, China, Taiwan" 
          class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-slate-800 text-xs font-semibold focus:outline-none focus:border-joule-blue"
        >
      </div>

      <!-- Model Number -->
      <div>
        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
          Model Number <span class="text-red-500">*</span>
        </label>
        <input 
          type="text" 
          name="model" 
          required 
          placeholder="e.g. VF-2SS, QT-250, HFP-200" 
          class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-slate-800 text-xs font-semibold focus:outline-none focus:border-joule-blue"
        >
      </div>

      <!-- Machine Condition -->
      <div>
        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
          Machine Condition <span class="text-red-500">*</span>
        </label>
        <select 
          name="condition" 
          required 
          class="w-full px-3 py-2.5 rounded-xl border border-slate-300 text-slate-800 text-xs font-semibold focus:outline-none focus:border-joule-blue bg-white"
        >
          <option value="Used" selected>Used (Running Condition)</option>
          <option value="New">New (Brand New Factory Unit)</option>
          <option value="Refurbished">Refurbished / Overhauled</option>
          <option value="Like New">Like New (Demo / Surplus)</option>
        </select>
      </div>

      <!-- Manufacturing Year -->
      <div>
        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
          Manufacturing Year <span class="text-red-500">*</span>
        </label>
        <input 
          type="number" 
          name="year" 
          required 
          min="1970" 
          max="<?php echo date('Y') + 1; ?>" 
          value="<?php echo date('Y') - 4; ?>" 
          class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-slate-800 text-xs font-semibold focus:outline-none focus:border-joule-blue"
        >
      </div>

      <!-- Quantity -->
      <div>
        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
          Available Quantity <span class="text-red-500">*</span>
        </label>
        <input 
          type="number" 
          name="quantity" 
          required 
          min="1" 
          value="1" 
          class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-slate-800 text-xs font-semibold focus:outline-none focus:border-joule-blue"
        >
      </div>

      <!-- Operating Hours -->
      <div>
        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
          Operating Hours / Run Time
        </label>
        <input 
          type="text" 
          name="hours_run" 
          placeholder="e.g. 4,200 hrs / Plant Maintained" 
          class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-slate-800 text-xs font-semibold focus:outline-none focus:border-joule-blue"
        >
      </div>

    </div>
  </div>

  <!-- ==================== SECTION 2: TECHNICAL SPECIFICATIONS ==================== -->
  <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
    <div class="bg-slate-50 border-b border-slate-200 px-6 py-4 flex items-center justify-between">
      <div class="flex items-center gap-3">
        <span class="w-7 h-7 rounded-lg bg-blue-600 text-white font-bold text-xs flex items-center justify-center">2</span>
        <div>
          <h3 class="font-bold text-sm text-slate-900">Technical Specifications</h3>
          <p class="text-[11px] text-slate-500">Engineering dimensions, capacities, power, and detailed specs</p>
        </div>
      </div>
      <span class="text-[11px] text-slate-400 font-semibold uppercase tracking-wider">Engineering Data</span>
    </div>

    <div class="p-6 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-6">
      
      <div>
        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
          Machine Capacity / Tonnage <span class="text-red-500">*</span>
        </label>
        <input 
          type="text" 
          name="capacity" 
          required 
          placeholder="e.g. 250 Tons / 3000 mm Bed" 
          class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-slate-800 text-xs font-semibold focus:outline-none focus:border-joule-blue"
        >
      </div>

      <div>
        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
          Power Rating (kW / HP) <span class="text-red-500">*</span>
        </label>
        <input 
          type="text" 
          name="power_rating" 
          required 
          placeholder="e.g. 22.4 kW / 30 HP" 
          class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-slate-800 text-xs font-semibold focus:outline-none focus:border-joule-blue"
        >
      </div>

      <div>
        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
          Operating Speed (RPM / SPM)
        </label>
        <input 
          type="text" 
          name="operating_speed" 
          placeholder="e.g. 12,000 RPM Spindle" 
          class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-slate-800 text-xs font-semibold focus:outline-none focus:border-joule-blue"
        >
      </div>

      <div>
        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
          Voltage / Frequency
        </label>
        <input 
          type="text" 
          name="voltage" 
          placeholder="e.g. 415V, 3-Phase, 50/60 Hz" 
          class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-slate-800 text-xs font-semibold focus:outline-none focus:border-joule-blue"
        >
      </div>

      <div>
        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
          Machine Dimensions (L x W x H) <span class="text-red-500">*</span>
        </label>
        <input 
          type="text" 
          name="dimensions" 
          required 
          placeholder="e.g. 3.2m x 2.4m x 2.8m" 
          class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-slate-800 text-xs font-semibold focus:outline-none focus:border-joule-blue"
        >
      </div>

      <div>
        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
          Total Weight (kg / tons) <span class="text-red-500">*</span>
        </label>
        <input 
          type="text" 
          name="machine_weight" 
          required 
          placeholder="e.g. 6,800 kg" 
          class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-slate-800 text-xs font-semibold focus:outline-none focus:border-joule-blue"
        >
      </div>

      <div>
        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
          Production Capacity
        </label>
        <input 
          type="text" 
          name="production_capacity" 
          placeholder="e.g. 450 parts / hr" 
          class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-slate-800 text-xs font-semibold focus:outline-none focus:border-joule-blue"
        >
      </div>

      <div>
        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
          Slide Stroke / Daylight
        </label>
        <input 
          type="text" 
          name="stroke_length" 
          placeholder="e.g. 180 mm / 500 mm" 
          class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-slate-800 text-xs font-semibold focus:outline-none focus:border-joule-blue"
        >
      </div>

      <!-- Technical Description -->
      <div class="sm:col-span-2 lg:col-span-4">
        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
          Comprehensive Technical Description <span class="text-red-500">*</span>
        </label>
        <textarea 
          name="technical_description" 
          rows="4" 
          required 
          placeholder="Describe machine history, controller type (e.g. Fanuc 31i, Siemens 840D), tooling packages included, maintenance logs, and plant availability." 
          class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-slate-800 text-xs font-semibold focus:outline-none focus:border-joule-blue"
        ></textarea>
      </div>

    </div>
  </div>

  <!-- ==================== SECTION 3: PRICE AND LOCATION ==================== -->
  <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
    <div class="bg-slate-50 border-b border-slate-200 px-6 py-4 flex items-center justify-between">
      <div class="flex items-center gap-3">
        <span class="w-7 h-7 rounded-lg bg-blue-600 text-white font-bold text-xs flex items-center justify-center">3</span>
        <div>
          <h3 class="font-bold text-sm text-slate-900">Price and Location</h3>
          <p class="text-[11px] text-slate-500">Commercial terms, currency, plant city, and shipping conditions</p>
        </div>
      </div>
      <span class="text-[11px] text-slate-400 font-semibold uppercase tracking-wider">Commercial</span>
    </div>

    <div class="p-6 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-6">
      
      <!-- Asking Price -->
      <div>
        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
          Asking Price <span class="text-red-500">*</span>
        </label>
        <input 
          type="number" 
          name="price" 
          required 
          placeholder="e.g. 1850000" 
          class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-slate-800 text-xs font-semibold focus:outline-none focus:border-joule-blue"
        >
      </div>

      <!-- Currency -->
      <div>
        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
          Currency <span class="text-red-500">*</span>
        </label>
        <select 
          name="currency" 
          required 
          class="w-full px-3 py-2.5 rounded-xl border border-slate-300 text-slate-800 text-xs font-semibold focus:outline-none focus:border-joule-blue bg-white"
        >
          <option value="INR" selected>INR (₹ Indian Rupee)</option>
          <option value="USD">USD ($ US Dollar)</option>
          <option value="EUR">EUR (€ Euro)</option>
        </select>
      </div>

      <!-- Price Type -->
      <div>
        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
          Price Type
        </label>
        <select 
          name="price_type" 
          class="w-full px-3 py-2.5 rounded-xl border border-slate-300 text-slate-800 text-xs font-semibold focus:outline-none focus:border-joule-blue bg-white"
        >
          <option value="Fixed Price">Fixed Price</option>
          <option value="Negotiable" selected>Negotiable (Best Offer)</option>
          <option value="Request for Price">Request for Price (RFQ)</option>
        </select>
      </div>

      <!-- Shipping Terms -->
      <div>
        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
          Shipping Terms
        </label>
        <select 
          name="shipping_terms" 
          class="w-full px-3 py-2.5 rounded-xl border border-slate-300 text-slate-800 text-xs font-semibold focus:outline-none focus:border-joule-blue bg-white"
        >
          <option value="Ex-Works (Plant Floor)" selected>Ex-Works (Plant Floor)</option>
          <option value="FOB (Port of Origin)">FOB (Port of Origin)</option>
          <option value="CIF (Destination Port)">CIF (Destination Port)</option>
          <option value="Loaded on Truck (LOT)">Loaded on Truck (LOT)</option>
        </select>
      </div>

      <!-- Country -->
      <div>
        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
          Plant Country <span class="text-red-500">*</span>
        </label>
        <select 
          name="country" 
          required 
          class="w-full px-3 py-2.5 rounded-xl border border-slate-300 text-slate-800 text-xs font-semibold focus:outline-none focus:border-joule-blue bg-white"
        >
          <option value="India" selected>🇮🇳 India</option>
          <option value="Japan">🇯🇵 Japan</option>
          <option value="Germany">🇩🇪 Germany</option>
          <option value="United States">🇺🇸 United States</option>
          <option value="China">🇨🇳 China</option>
          <option value="South Korea">🇰🇷 South Korea</option>
          <option value="Taiwan">🇹🇼 Taiwan</option>
          <option value="Italy">🇮🇹 Italy</option>
          <option value="Singapore">🇸🇬 Singapore</option>
        </select>
      </div>

      <!-- State -->
      <div>
        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
          State / Province
        </label>
        <input 
          type="text" 
          name="state" 
          placeholder="e.g. Maharashtra, Aichi, Bavaria" 
          class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-slate-800 text-xs font-semibold focus:outline-none focus:border-joule-blue"
        >
      </div>

      <!-- City -->
      <div class="sm:col-span-2">
        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
          City / Industrial Facility Location <span class="text-red-500">*</span>
        </label>
        <input 
          type="text" 
          name="city" 
          required 
          placeholder="e.g. Pune, Chakan Industrial Area, Phase II" 
          class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-slate-800 text-xs font-semibold focus:outline-none focus:border-joule-blue"
        >
      </div>

    </div>
  </div>

  <!-- ==================== SECTION 4: UPLOAD IMAGES AND VIDEOS ==================== -->
  <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
    <div class="bg-slate-50 border-b border-slate-200 px-6 py-4 flex items-center justify-between">
      <div class="flex items-center gap-3">
        <span class="w-7 h-7 rounded-lg bg-blue-600 text-white font-bold text-xs flex items-center justify-center">4</span>
        <div>
          <h3 class="font-bold text-sm text-slate-900">Upload Images and Videos</h3>
          <p class="text-[11px] text-slate-500">Main photo, additional gallery photos, working video, nameplate photo &amp; PDF</p>
        </div>
      </div>
      <span class="text-[11px] text-slate-400 font-semibold uppercase tracking-wider">
        Up to <?php echo $userSub['photo_limit']; ?> Photos (<?php echo $userSub['plan_name']; ?>)
      </span>
    </div>

    <div class="p-6 space-y-6">
      
      <!-- Multi-image Upload Box -->
      <div>
        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">
          Machine Photos (Main &amp; Gallery - Up to <?php echo $userSub['photo_limit']; ?> Photos) <span class="text-red-500">*</span>
        </label>
        <div class="border-2 border-dashed border-slate-300 rounded-2xl p-6 text-center hover:border-blue-500 transition-colors bg-slate-50/50">
          <div class="w-12 h-12 rounded-2xl bg-blue-100 text-blue-600 flex items-center justify-center mx-auto mb-3 text-xl">
            <i class="fa-solid fa-cloud-arrow-up"></i>
          </div>
          <div class="text-xs font-bold text-slate-800 mb-1">
            Choose high-resolution photos of your machine
          </div>
          <p class="text-[11px] text-slate-500 mb-4">
            Supports JPG, PNG, WEBP. Include front view, side angles, operator console, bed condition, and nameplate.
          </p>
          <input 
            type="file" 
            name="machine_images[]" 
            multiple 
            accept="image/*" 
            required
            class="text-xs text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-blue-600 file:text-white hover:file:bg-blue-700 cursor-pointer"
          >
        </div>
      </div>

      <!-- Machine Working Video & Nameplate Upload -->
      <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 sm:gap-6 pt-2">
        <div>
          <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
            Machine Working Video (YouTube / Vimeo / Drive URL)
          </label>
          <input 
            type="url" 
            name="video_url" 
            placeholder="https://www.youtube.com/watch?v=..." 
            class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-slate-800 text-xs font-semibold focus:outline-none focus:border-joule-blue"
          >
          <p class="text-[10px] text-slate-500 mt-1">Video links give 3x higher verified buyer conversion.</p>
        </div>

        <div>
          <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
            Technical Brochure or Inspection PDF (Optional)
          </label>
          <input 
            type="file" 
            name="technical_pdf" 
            accept=".pdf,.doc,.docx" 
            class="w-full text-xs text-slate-500 file:mr-3 file:py-2 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-slate-200 file:text-slate-800"
          >
        </div>
      </div>

    </div>
  </div>

  <!-- ==================== SECTION 5: CONTACT INFORMATION ==================== -->
  <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
    <div class="bg-slate-50 border-b border-slate-200 px-6 py-4 flex items-center justify-between">
      <div class="flex items-center gap-3">
        <span class="w-7 h-7 rounded-lg bg-blue-600 text-white font-bold text-xs flex items-center justify-center">5</span>
        <div>
          <h3 class="font-bold text-sm text-slate-900">Contact Information</h3>
          <p class="text-[11px] text-slate-500">Plant authorized representative for buyer inquiry notifications</p>
        </div>
      </div>
      <span class="text-[11px] text-slate-400 font-semibold uppercase tracking-wider">Authorized</span>
    </div>

    <div class="p-6 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-6">
      
      <div>
        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
          Contact Person <span class="text-red-500">*</span>
        </label>
        <input 
          type="text" 
          name="contact_person" 
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
          name="company_name" 
          value="<?php echo htmlspecialchars($currentUser['company'] ?? ''); ?>" 
          required 
          class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-slate-800 text-xs font-semibold focus:outline-none focus:border-joule-blue"
        >
      </div>

      <div>
        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
          Seller Company Location
        </label>
        <input 
          type="text" 
          name="seller_location" 
          placeholder="e.g. Chennai, India / Shanghai, China" 
          class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-slate-800 text-xs font-semibold focus:outline-none focus:border-joule-blue"
        >
      </div>

      <div>
        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
          Business Email <span class="text-red-500">*</span>
        </label>
        <input 
          type="email" 
          name="business_email" 
          value="<?php echo htmlspecialchars($currentUser['email'] ?? ''); ?>" 
          required 
          class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-slate-800 text-xs font-semibold focus:outline-none focus:border-joule-blue"
        >
      </div>

      <div>
        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
          Mobile Number <span class="text-red-500">*</span>
        </label>
        <input 
          type="tel" 
          name="mobile_number" 
          value="<?php echo htmlspecialchars($currentUser['phone'] ?? ''); ?>" 
          required 
          class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-slate-800 text-xs font-semibold focus:outline-none focus:border-joule-blue"
        >
      </div>

    </div>
  </div>

  <!-- FINAL ACTION BUTTONS (Section 5 Specification) -->
  <div class="bg-white rounded-2xl p-5 border border-slate-200 shadow-xs flex flex-col sm:flex-row items-center justify-between gap-4">
    <div class="text-xs text-slate-500">
      <i class="fa-solid fa-shield-halved text-emerald-600 mr-1"></i>
      Submitted machinery undergoes verification by our industrial engineering desk before going live.
    </div>

    <div class="flex items-center gap-3 w-full sm:w-auto">
      <!-- Save as Draft Button -->
      <button 
        type="submit" 
        name="is_draft" 
        value="1" 
        class="w-full sm:w-auto bg-slate-100 hover:bg-slate-200 text-slate-800 font-bold text-xs py-3 px-5 rounded-xl border border-slate-300 transition-all cursor-pointer"
      >
        <i class="fa-regular fa-bookmark mr-1"></i>
        <span>Save as Draft</span>
      </button>

      <!-- Submit for Approval Button -->
      <button 
        type="submit" 
        class="w-full sm:w-auto bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs py-3 px-6 rounded-xl shadow-md transition-all cursor-pointer inline-flex items-center justify-center gap-2"
      >
        <i class="fa-solid fa-paper-plane text-[11px]"></i>
        <span>Submit for Approval</span>
      </button>
    </div>
  </div>

</form>

<?php
require_once __DIR__ . '/footer.php';
?>
