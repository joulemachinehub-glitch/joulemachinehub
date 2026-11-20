<?php
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https://" : "http://";
$host = $_SERVER['HTTP_HOST'] ?? 'joulemachinehub.com';
$pageTitle = "Register Company Account | Joule Machine Hub";
$pageDesc = "Create your Joule Machine Hub account. Upload and manage industrial machinery, receive qualified buyer inquiries, and promote plant equipment worldwide.";
$canonicalUrl = $protocol . $host . "/register";
$pageRobots = "noindex, nofollow, noarchive, nosnippet";

require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/data/machines.php';

$error = '';
$success = '';

if (isLoggedIn()) {
    header('Location: dashboard/index.php');
    exit;
}

if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
    $name            = isset($_POST['name']) ? trim($_POST['name']) : '';
    $company         = isset($_POST['company']) ? trim($_POST['company']) : '';
    $email           = isset($_POST['email']) ? trim($_POST['email']) : '';
    $phone           = isset($_POST['phone']) ? trim($_POST['phone']) : '';
    $country         = isset($_POST['country']) ? trim($_POST['country']) : 'India';
    $state           = isset($_POST['state']) ? trim($_POST['state']) : '';
    $city            = isset($_POST['city']) ? trim($_POST['city']) : '';
    $cityCustom      = isset($_POST['city_custom']) ? trim($_POST['city_custom']) : '';
    if (($city === 'OTHER' || empty($city)) && !empty($cityCustom)) {
        $city = $cityCustom;
    }
    $password        = isset($_POST['password']) ? trim($_POST['password']) : '';
    $confirmPassword = isset($_POST['confirm_password']) ? trim($_POST['confirm_password']) : '';
    $terms           = isset($_POST['terms']) ? true : false;
    $role            = 'seller';

    if (empty($name) || empty($company) || empty($email) || empty($phone) || empty($country) || empty($password) || empty($confirmPassword)) {
        $error = 'Please fill in all required personal and company fields marked with (*).';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid business email address.';
    } elseif (strlen(preg_replace('/[^0-9]/', '', $phone)) < 7) {
        $error = 'Please enter a valid mobile phone number for SMS verification.';
    } elseif ($password !== $confirmPassword) {
        $error = 'Passwords do not match. Please verify and re-enter.';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters long for security.';
    } elseif (!$terms) {
        $error = 'You must agree to the Terms & Conditions and Privacy Policy to create an account.';
    } else {
        $finalCity = $city ?: ($state ?: $country);
        $regPayload = [
            'name'     => $name,
            'company'  => $company,
            'email'    => $email,
            'phone'    => $phone,
            'role'     => 'seller',
            'country'  => $country,
            'state'    => $state,
            'city'     => $finalCity,
            'password' => $password,
            'gst'      => $_POST['gst'] ?? ''
        ];
        
        $otpResult = generateSellerRegistrationOtp($regPayload);
        if ($otpResult['success']) {
            header('Location: verify-otp.php');
            exit;
        } else {
            $error = $otpResult['message'];
        }
    }
}

require_once __DIR__ . '/includes/header.php';
?>

<div class="py-10 sm:py-16 px-4 bg-slate-900 flex-1 flex items-center justify-center min-h-[85vh]">
  <div class="max-w-xl w-full bg-white rounded-3xl border-2 border-joule-yellow shadow-2xl overflow-hidden animate-in fade-in duration-300">
    
    <!-- Top Brand Header Banner -->
    <div class="bg-joule-blue text-white p-6 sm:p-8 text-center border-b-4 border-joule-yellow relative">
      <div class="w-16 h-16 rounded-2xl bg-joule-yellow text-slate-950 flex items-center justify-center font-black text-2xl mx-auto mb-3 shadow-lg border-2 border-amber-300">
        <i class="fa-solid fa-industry"></i>
      </div>
      <span class="inline-block uppercase tracking-wider text-[10px] font-black bg-white/20 text-yellow-300 px-3 py-1 rounded-full mb-2">
        Industrial Seller &amp; OEM Registration
      </span>
      <h1 class="text-2xl sm:text-3xl font-black text-white">Create Company Account</h1>
      <p class="text-xs text-blue-200 mt-1.5 max-w-sm mx-auto">
        Join verified machine sellers across India, Japan, Germany &amp; worldwide. Upload equipment and connect with qualified buyers through our mediated B2B desk.
      </p>
    </div>

    <!-- Registration Form Body -->
    <div class="p-6 sm:p-8 space-y-6">
      
      <!-- Seller Quality Gate Notice -->
      <div class="p-3.5 rounded-2xl bg-blue-50/80 border border-blue-200 text-slate-800 text-xs flex items-start gap-3">
        <div class="w-7 h-7 rounded-lg bg-joule-blue text-yellow-400 flex items-center justify-center text-xs shrink-0 mt-0.5">
          <i class="fa-solid fa-shield-halved"></i>
        </div>
        <div class="leading-relaxed">
          <span class="font-extrabold text-joule-blue block">Dual Seller Quality Verification:</span>
          To protect buyers and ensure authentic equipment listings, 6-digit verification codes will be dispatched to both your <strong>business email</strong> and <strong>mobile number</strong> to activate your seller dashboard.
        </div>
      </div>

      <?php if (!empty($error)): ?>
        <div class="p-4 rounded-xl bg-red-50 border border-red-200 text-red-700 text-xs font-semibold flex items-center gap-3">
          <i class="fa-solid fa-circle-exclamation text-base shrink-0"></i>
          <div><?php echo htmlspecialchars($error); ?></div>
        </div>
      <?php endif; ?>

      <form action="register.php" method="POST" class="space-y-4">
        
        <!-- Row 1: Full Name & Company Name -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <div>
            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
              Full Name <span class="text-red-500">*</span>
            </label>
            <div class="relative">
              <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-slate-400">
                <i class="fa-solid fa-user text-xs"></i>
              </span>
              <input 
                type="text" 
                name="name" 
                value="<?php echo htmlspecialchars($_POST['name'] ?? ''); ?>" 
                required 
                placeholder="e.g. Rajesh Kumar" 
                class="w-full pl-9 pr-3 py-2.5 rounded-xl border border-slate-300 text-slate-800 text-xs font-semibold focus:outline-none focus:border-joule-blue focus:ring-1 focus:ring-joule-blue"
              >
            </div>
          </div>

          <div>
            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
              Company Name <span class="text-red-500">*</span>
            </label>
            <div class="relative">
              <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-slate-400">
                <i class="fa-solid fa-building text-xs"></i>
              </span>
              <input 
                type="text" 
                name="company" 
                value="<?php echo htmlspecialchars($_POST['company'] ?? ''); ?>" 
                required 
                placeholder="e.g. Precision Forge Tech Ltd" 
                class="w-full pl-9 pr-3 py-2.5 rounded-xl border border-slate-300 text-slate-800 text-xs font-semibold focus:outline-none focus:border-joule-blue focus:ring-1 focus:ring-joule-blue"
              >
            </div>
          </div>
        </div>

        <!-- Row 2: Email Address & Mobile Number -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <div>
            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
              Email Address <span class="text-red-500">*</span>
            </label>
            <div class="relative">
              <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-slate-400">
                <i class="fa-solid fa-envelope text-xs"></i>
              </span>
              <input 
                type="email" 
                name="email" 
                value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" 
                required 
                placeholder="name@company.com" 
                class="w-full pl-9 pr-3 py-2.5 rounded-xl border border-slate-300 text-slate-800 text-xs font-semibold focus:outline-none focus:border-joule-blue focus:ring-1 focus:ring-joule-blue"
              >
            </div>
          </div>

          <div>
            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
              Mobile Number <span class="text-red-500">*</span>
            </label>
            <div class="relative">
              <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-slate-400">
                <i class="fa-solid fa-phone text-xs"></i>
              </span>
              <input 
                type="tel" 
                id="reg_phone"
                name="phone" 
                value="<?php echo htmlspecialchars($_POST['phone'] ?? ''); ?>" 
                required 
                placeholder="+91 98765 43210" 
                class="w-full pl-9 pr-3 py-2.5 rounded-xl border border-slate-300 text-slate-800 text-xs font-semibold focus:outline-none focus:border-joule-blue focus:ring-1 focus:ring-joule-blue"
              >
            </div>
          </div>
        </div>

        <!-- Row 3: Country, State, City (with Automatic Detection) -->
        <div class="space-y-3 bg-slate-50 p-4 rounded-2xl border border-slate-200">
          <div class="flex items-center justify-between flex-wrap gap-2">
            <div class="text-[11px] font-black uppercase tracking-wider text-slate-700 flex items-center gap-1.5">
              <i class="fa-solid fa-location-dot text-joule-brightblue"></i>
              <span>Plant &amp; Facility Location (Auto-Detected)</span>
            </div>
            <button 
              type="button" 
              onclick="autoDetectLocation(true)" 
              class="text-[11px] font-bold text-joule-brightblue hover:text-blue-800 flex items-center gap-1 bg-blue-50 hover:bg-blue-100 px-2.5 py-1 rounded-lg border border-blue-200 transition-colors cursor-pointer"
            >
              <i class="fa-solid fa-crosshairs"></i>
              <span>Auto-Detect</span>
            </button>
          </div>

          <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
            <!-- 1. Country -->
            <div>
              <label class="block text-[11px] font-bold text-slate-700 uppercase tracking-wider mb-1">
                Country <span class="text-red-500">*</span>
              </label>
              <div class="relative">
                <span class="absolute inset-y-0 left-0 flex items-center pl-2.5 text-slate-400 pointer-events-none">
                  <i class="fa-solid fa-globe text-xs"></i>
                </span>
                <select 
                  id="reg_country" 
                  name="country" 
                  required 
                  class="w-full pl-8 pr-6 py-2.5 rounded-xl border border-slate-300 text-slate-800 text-xs font-semibold focus:outline-none focus:border-joule-blue focus:ring-1 focus:ring-joule-blue bg-white"
                >
                  <!-- Populated by JS -->
                </select>
              </div>
            </div>

            <!-- 2. State / Province -->
            <div>
              <label class="block text-[11px] font-bold text-slate-700 uppercase tracking-wider mb-1">
                State / Province <span class="text-red-500">*</span>
              </label>
              <div class="relative">
                <span class="absolute inset-y-0 left-0 flex items-center pl-2.5 text-slate-400 pointer-events-none">
                  <i class="fa-solid fa-map text-xs"></i>
                </span>
                <select 
                  id="reg_state" 
                  name="state" 
                  required 
                  class="w-full pl-8 pr-6 py-2.5 rounded-xl border border-slate-300 text-slate-800 text-xs font-semibold focus:outline-none focus:border-joule-blue focus:ring-1 focus:ring-joule-blue bg-white"
                >
                  <!-- Populated dynamically based on Country -->
                </select>
              </div>
            </div>

            <!-- 3. City / Industrial Hub -->
            <div>
              <label class="block text-[11px] font-bold text-slate-700 uppercase tracking-wider mb-1">
                City / Hub <span class="text-red-500">*</span>
              </label>
              <div class="relative">
                <span class="absolute inset-y-0 left-0 flex items-center pl-2.5 text-slate-400 pointer-events-none">
                  <i class="fa-solid fa-city text-xs"></i>
                </span>
                <select 
                  id="reg_city" 
                  name="city" 
                  required 
                  class="w-full pl-8 pr-6 py-2.5 rounded-xl border border-slate-300 text-slate-800 text-xs font-semibold focus:outline-none focus:border-joule-blue focus:ring-1 focus:ring-joule-blue bg-white"
                >
                  <!-- Populated dynamically based on State -->
                </select>
              </div>
            </div>
          </div>

          <!-- Custom City Input (Shown when "Other City" is selected) -->
          <div id="custom_city_box" class="hidden pt-1">
            <label class="block text-[11px] font-bold text-slate-600 mb-1">
              Enter Custom City / Industrial Area:
            </label>
            <input 
              type="text" 
              id="reg_city_custom" 
              name="city_custom" 
              placeholder="e.g. Madurai Ring Road Industrial Cluster" 
              class="w-full px-3 py-2 rounded-xl border border-slate-300 text-slate-800 text-xs font-semibold focus:outline-none focus:border-joule-blue"
            >
          </div>

          <div class="flex items-center gap-1.5 text-[10px] text-slate-500">
            <i class="fa-solid fa-circle-check text-emerald-500 text-[9px]"></i>
            <span id="location_preview_text">Selected: India &bull; Tamil Nadu &bull; Madurai</span>
          </div>
        </div>

        <!-- Row 4: Password & Confirm Password -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <div>
            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
              Password <span class="text-red-500">*</span>
            </label>
            <div class="relative">
              <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-slate-400">
                <i class="fa-solid fa-lock text-xs"></i>
              </span>
              <input 
                type="password" 
                name="password" 
                required 
                placeholder="Min 6 characters" 
                class="w-full pl-9 pr-3 py-2.5 rounded-xl border border-slate-300 text-slate-800 text-xs font-semibold focus:outline-none focus:border-joule-blue focus:ring-1 focus:ring-joule-blue"
              >
            </div>
          </div>

          <div>
            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
              Confirm Password <span class="text-red-500">*</span>
            </label>
            <div class="relative">
              <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-slate-400">
                <i class="fa-solid fa-shield-halved text-xs"></i>
              </span>
              <input 
                type="password" 
                name="confirm_password" 
                required 
                placeholder="Re-enter password" 
                class="w-full pl-9 pr-3 py-2.5 rounded-xl border border-slate-300 text-slate-800 text-xs font-semibold focus:outline-none focus:border-joule-blue focus:ring-1 focus:ring-joule-blue"
              >
            </div>
          </div>
        </div>

        <!-- Terms and Conditions Checkbox -->
        <div class="pt-2">
          <label class="flex items-start gap-2.5 cursor-pointer text-xs text-slate-600">
            <input 
              type="checkbox" 
              name="terms" 
              value="1" 
              required 
              class="w-4 h-4 rounded text-blue-600 border-slate-300 focus:ring-blue-500 mt-0.5 shrink-0"
            >
            <span>
              I agree to the <a href="index.php#faqs" class="text-blue-600 underline font-bold">Terms &amp; Conditions</a> and <a href="index.php#faqs" class="text-blue-600 underline font-bold">Privacy Policy</a> of Joule Machine Hub.
            </span>
          </label>
        </div>

        <!-- Create Account Button -->
        <div class="pt-2">
          <button 
            type="submit" 
            class="w-full bg-joule-yellow hover:bg-amber-400 text-slate-950 font-black text-sm py-3.5 px-6 rounded-2xl shadow-lg shadow-amber-400/20 transition-all flex items-center justify-center gap-2 cursor-pointer"
          >
            <i class="fa-solid fa-user-plus"></i>
            <span>Create Account</span>
          </button>
        </div>
      </form>

      <!-- Already Have an Account Link -->
      <div class="pt-4 border-t border-slate-200 text-center">
        <p class="text-xs text-slate-600">
          Already have an account? 
          <a href="login.php" class="text-blue-600 hover:text-blue-800 font-black ml-1">
            Login
          </a>
        </p>
      </div>

    </div>
  </div>
</div>

<script>
// Comprehensive Global Location Data (Overall Countries, States & Industrial Hub Cities)
const LOCATION_DATA = {
  "India": {
    "code": "IN",
    "flag": "🇮🇳",
    "dial": "+91",
    "states": {
      "Tamil Nadu": [
        "Madurai", "Chennai", "Coimbatore", "Tiruchirappalli", "Salem", 
        "Tiruppur", "Erode", "Vellore", "Thoothukudi (Tuticorin)", "Hosur", 
        "Dindigul", "Thanjavur", "Ranipet", "Karur", "Nagercoil", "Kanchipuram", 
        "Sivakasi", "Cuddalore", "Kumbakonam", "Tirunelveli", "Pollachi"
      ],
      "Maharashtra": [
        "Mumbai", "Pune", "Thane", "Nagpur", "Nashik", "Aurangabad (Chhatrapati Sambhajinagar)", 
        "Kolhapur", "Solapur", "Navi Mumbai", "Pimpri-Chinchwad", "Ahmednagar", "Waluj", "Tarapur"
      ],
      "Gujarat": [
        "Ahmedabad", "Surat", "Vadodara", "Rajkot", "Bhavnagar", "Jamnagar", 
        "Gandhinagar", "Vapi", "Ankleshwar", "Morbi", "Bharuch", "Sanand", "Halol"
      ],
      "Karnataka": [
        "Bengaluru", "Peenya", "Mysuru", "Hubballi-Dharwad", "Mangaluru", 
        "Belagavi", "Davanagere", "Ballari", "Tumakuru", "Shivamogga", "Bommasandra"
      ],
      "Delhi NCR": [
        "New Delhi", "Delhi", "Okhla", "Mayapuri", "Naraina", "Patparganj", "Kirti Nagar", "Bawana"
      ],
      "Haryana": [
        "Gurugram", "Faridabad", "Manesar", "Panipat", "Sonipat", "Ambala", "Rohtak", "Bahadurgarh"
      ],
      "Uttar Pradesh": [
        "Noida", "Greater Noida", "Kanpur", "Lucknow", "Ghaziabad", "Agra", "Varanasi", "Meerut", "Aligarh"
      ],
      "Telangana": [
        "Hyderabad", "Secunderabad", "Warangal", "Nizamabad", "Karimnagar", "Medchal", "Patancheru"
      ],
      "Andhra Pradesh": [
        "Visakhapatnam", "Vijayawada", "Guntur", "Nellore", "Tirupati", "Kakinada", "Rajahmundry", "Sri City"
      ],
      "West Bengal": [
        "Kolkata", "Howrah", "Durgapur", "Asansol", "Siliguri", "Haldia"
      ],
      "Rajasthan": [
        "Jaipur", "Jodhpur", "Kota", "Bhiwadi", "Udaipur", "Ajmer", "Alwar", "Bhilwara"
      ],
      "Punjab": [
        "Ludhiana", "Amritsar", "Jalandhar", "Patiala", "Bathinda", "Mohali", "Mandi Gobindgarh"
      ],
      "Kerala": [
        "Kochi", "Thiruvananthapuram", "Kozhikode", "Thrissur", "Kollam", "Palakkad"
      ],
      "Madhya Pradesh": [
        "Indore", "Bhopal", "Pithampur", "Gwalior", "Jabalpur", "Ujjain"
      ],
      "Odisha": [
        "Bhubaneswar", "Cuttack", "Rourkela", "Sambalpur", "Jharsuguda"
      ],
      "Chhattisgarh": [
        "Raipur", "Bhilai", "Durg", "Bilaspur", "Korba"
      ],
      "Jharkhand": [
        "Jamshedpur", "Ranchi", "Dhanbad", "Bokaro Steel City"
      ],
      "Uttarakhand": [
        "Dehradun", "Haridwar", "Rudrapur", "Pantnagar", "Roorkee"
      ],
      "Himachal Pradesh": [
        "Baddi", "Shimla", "Solan", "Kala Amb", "Paonta Sahib"
      ],
      "Goa": [
        "Panaji", "Margao", "Vasco da Gama", "Verna"
      ],
      "Bihar": [
        "Patna", "Muzaffarpur", "Gaya", "Bhagalpur"
      ],
      "Assam": [
        "Guwahati", "Dibrugarh", "Silchar", "Tinsukia"
      ],
      "Puducherry": [
        "Puducherry", "Karaikal"
      ],
      "Chandigarh": [
        "Chandigarh Industrial Area"
      ]
    }
  },
  "Japan": {
    "code": "JP",
    "flag": "🇯🇵",
    "dial": "+81",
    "states": {
      "Aichi Prefecture": ["Nagoya", "Toyota", "Okazaki", "Ichinomiya", "Toyohashi"],
      "Tokyo Metropolis": ["Tokyo", "Ota Ward", "Shinagawa", "Koto Ward", "Hachioji"],
      "Osaka Prefecture": ["Osaka", "Sakai", "Higashiosaka", "Kadoma", "Suita"],
      "Kanagawa Prefecture": ["Yokohama", "Kawasaki", "Sagamihara", "Fujisawa"],
      "Kyoto Prefecture": ["Kyoto", "Uji", "Kameoka"],
      "Ishikawa Prefecture": ["Kanazawa", "Komatsu", "Hakusan"],
      "Shizuoka Prefecture": ["Hamamatsu", "Shizuoka", "Fuji"],
      "Saitama Prefecture": ["Saitama", "Kawaguchi", "Kawagoe"],
      "Hyogo Prefecture": ["Kobe", "Himeji", "Amagasaki"],
      "Fukuoka Prefecture": ["Fukuoka", "Kitakyushu"],
      "Hiroshima Prefecture": ["Hiroshima", "Fukuyama"]
    }
  },
  "Germany": {
    "code": "DE",
    "flag": "🇩🇪",
    "dial": "+49",
    "states": {
      "Bavaria": ["Munich", "Nuremberg", "Augsburg", "Regensburg", "Ingolstadt"],
      "Baden-Württemberg": ["Stuttgart", "Mannheim", "Karlsruhe", "Freiburg", "Ulm"],
      "North Rhine-Westphalia": ["Düsseldorf", "Cologne", "Dortmund", "Essen", "Duisburg"],
      "Hesse": ["Frankfurt am Main", "Wiesbaden", "Kassel", "Darmstadt"],
      "Saxony": ["Dresden", "Leipzig", "Chemnitz"],
      "Lower Saxony": ["Hanover", "Braunschweig", "Wolfsburg"],
      "Berlin": ["Berlin"],
      "Hamburg": ["Hamburg"]
    }
  },
  "United States": {
    "code": "US",
    "flag": "🇺🇸",
    "dial": "+1",
    "states": {
      "Illinois": ["Chicago", "Rockford", "Elgin", "Peoria"],
      "Michigan": ["Detroit", "Grand Rapids", "Warren", "Sterling Heights"],
      "Ohio": ["Cleveland", "Cincinnati", "Columbus", "Dayton"],
      "California": ["Los Angeles", "San Jose", "San Diego", "San Francisco"],
      "Texas": ["Houston", "Dallas", "Austin", "Fort Worth"],
      "Pennsylvania": ["Pittsburgh", "Philadelphia", "Allentown"],
      "Indiana": ["Indianapolis", "Fort Wayne", "Elkhart"],
      "Wisconsin": ["Milwaukee", "Madison", "Green Bay"],
      "New York": ["New York", "Buffalo", "Rochester"],
      "North Carolina": ["Charlotte", "Raleigh", "Greensboro"],
      "Georgia": ["Atlanta", "Savannah"]
    }
  },
  "China": {
    "code": "CN",
    "flag": "🇨🇳",
    "dial": "+86",
    "states": {
      "Shanghai": ["Shanghai", "Pudong", "Minhang", "Jiading"],
      "Guangdong": ["Shenzhen", "Guangzhou", "Dongguan", "Foshan", "Zhongshan"],
      "Zhejiang": ["Ningbo", "Hangzhou", "Wenzhou", "Jiaxing", "Taizhou"],
      "Jiangsu": ["Suzhou", "Wuxi", "Nanjing", "Changzhou"],
      "Hebei": ["Shijiazhuang", "Tangshan", "Baoding"],
      "Shandong": ["Qingdao", "Jinan", "Yantai"]
    }
  },
  "South Korea": {
    "code": "KR",
    "flag": "🇰🇷",
    "dial": "+82",
    "states": {
      "Gyeonggi-do": ["Suwon", "Seongnam", "Goyang", "Ansan", "Hwaseong", "Pyeongtaek"],
      "Seoul": ["Seoul", "Gangnam", "Guro", "Geumcheon"],
      "Busan": ["Busan", "Sasang", "Gangseo"],
      "Incheon": ["Incheon", "Namdong Industrial Complex"],
      "Gyeongsangnam-do": ["Changwon", "Gimhae"],
      "Daegu": ["Daegu"],
      "Ulsan": ["Ulsan"]
    }
  },
  "Taiwan": {
    "code": "TW",
    "flag": "🇹🇼",
    "dial": "+886",
    "states": {
      "Taichung": ["Taichung", "Daya", "Taiping", "Wuri"],
      "Taipei": ["Taipei", "Neihu"],
      "New Taipei": ["New Taipei", "Wugu", "Xinzhuang"],
      "Kaohsiung": ["Kaohsiung", "Renwu", "Daliao"],
      "Tainan": ["Tainan", "Yongkang"],
      "Taoyuan": ["Taoyuan", "Zhongli", "Guishan"],
      "Hsinchu": ["Hsinchu Science Park", "Zhubei"]
    }
  },
  "Italy": {
    "code": "IT",
    "flag": "🇮🇹",
    "dial": "+39",
    "states": {
      "Lombardy": ["Milan", "Brescia", "Bergamo", "Monza"],
      "Emilia-Romagna": ["Bologna", "Modena", "Parma", "Reggio Emilia"],
      "Piedmont": ["Turin", "Novara", "Alessandria"],
      "Veneto": ["Venice", "Verona", "Vicenza", "Padua"]
    }
  },
  "United Kingdom": {
    "code": "GB",
    "flag": "🇬🇧",
    "dial": "+44",
    "states": {
      "West Midlands": ["Birmingham", "Coventry", "Wolverhampton"],
      "Greater Manchester": ["Manchester", "Salford", "Bolton"],
      "Yorkshire": ["Sheffield", "Leeds", "Bradford"],
      "Greater London": ["London", "Croydon", "Park Royal"],
      "Scotland": ["Glasgow", "Edinburgh", "Aberdeen"]
    }
  },
  "Switzerland": {
    "code": "CH",
    "flag": "🇨🇭",
    "dial": "+41",
    "states": {
      "Canton of Bern": ["Moutier", "Biel/Bienne", "Bern"],
      "Canton of Zurich": ["Zurich", "Winterthur"],
      "Canton of Basel": ["Basel", "Liestal"],
      "Canton of Geneva": ["Geneva", "Meyrin"],
      "Canton of Vaud": ["Lausanne"],
      "Canton of St. Gallen": ["St. Gallen"]
    }
  },
  "Singapore": {
    "code": "SG",
    "flag": "🇸🇬",
    "dial": "+65",
    "states": {
      "Western Region": ["Jurong Industrial Estate", "Tuas", "Pioneer"],
      "Central Region": ["Singapore City", "Kallang"],
      "Northern Region": ["Woodlands", "Sembawang"],
      "Eastern Region": ["Changi", "Tampines"]
    }
  },
  "United Arab Emirates": {
    "code": "AE",
    "flag": "🇦🇪",
    "dial": "+971",
    "states": {
      "Dubai": ["Dubai", "Jebel Ali Free Zone (JAFZA)", "Al Quoz", "Dubai South"],
      "Abu Dhabi": ["Abu Dhabi", "Musaffah", "KIZAD", "ICAD"],
      "Sharjah": ["Sharjah Industrial Areas", "SAIF Zone"]
    }
  },
  "Vietnam": {
    "code": "VN",
    "flag": "🇻🇳",
    "dial": "+84",
    "states": {
      "Ho Chi Minh City": ["Ho Chi Minh City", "Tan Binh", "Thu Duc"],
      "Binh Duong": ["Thu Dau Mot", "VSIP Industrial Park", "Ben Cat"],
      "Dong Nai": ["Bien Hoa", "Long Thanh"],
      "Hanoi": ["Hanoi", "Bac Tu Liem"],
      "Hai Phong": ["Hai Phong", "Dinh Vu"]
    }
  },
  "Malaysia": {
    "code": "MY",
    "flag": "🇲🇾",
    "dial": "+60",
    "states": {
      "Selangor": ["Shah Alam", "Petaling Jaya", "Klang"],
      "Penang": ["George Town", "Bayan Lepas", "Batu Kawan"],
      "Johor": ["Johor Bahru", "Pasir Gudang"],
      "Kuala Lumpur": ["Kuala Lumpur"]
    }
  },
  "Turkey": {
    "code": "TR",
    "flag": "🇹🇷",
    "dial": "+90",
    "states": {
      "Marmara": ["Istanbul", "Bursa", "Kocaeli (Gebze)"],
      "Aegean": ["Izmir", "Manisa"],
      "Central Anatolia": ["Ankara", "Konya"]
    }
  },
  "Spain": {
    "code": "ES",
    "flag": "🇪🇸",
    "dial": "+34",
    "states": {
      "Catalonia": ["Barcelona", "Sabadell", "Terrassa"],
      "Madrid": ["Madrid", "Getafe", "Leganes"],
      "Basque Country": ["Bilbao", "Vitoria-Gasteiz"],
      "Valencia": ["Valencia", "Alicante"]
    }
  },
  "France": {
    "code": "FR",
    "flag": "🇫🇷",
    "dial": "+33",
    "states": {
      "Île-de-France": ["Paris", "Boulogne-Billancourt"],
      "Auvergne-Rhône-Alpes": ["Lyon", "Grenoble"],
      "Occitanie": ["Toulouse", "Montpellier"]
    }
  },
  "Canada": {
    "code": "CA",
    "flag": "🇨🇦",
    "dial": "+1",
    "states": {
      "Ontario": ["Toronto", "Mississauga", "Brampton", "Hamilton", "Windsor"],
      "Quebec": ["Montreal", "Quebec City"],
      "British Columbia": ["Vancouver", "Surrey"],
      "Alberta": ["Calgary", "Edmonton"]
    }
  },
  "Australia": {
    "code": "AU",
    "flag": "🇦🇺",
    "dial": "+61",
    "states": {
      "Victoria": ["Melbourne", "Dandenong", "Geelong"],
      "New South Wales": ["Sydney", "Parramatta", "Newcastle"],
      "Queensland": ["Brisbane"],
      "Western Australia": ["Perth"]
    }
  },
  "Saudi Arabia": {
    "code": "SA",
    "flag": "🇸🇦",
    "dial": "+966",
    "states": {
      "Eastern Province": ["Dammam", "Jubail Industrial City", "Al Khobar"],
      "Riyadh Region": ["Riyadh", "Second Industrial City"],
      "Makkah Region": ["Jeddah", "Yanbu Industrial City"]
    }
  },
  "Brazil": {
    "code": "BR",
    "flag": "🇧🇷",
    "dial": "+55",
    "states": {
      "São Paulo": ["São Paulo", "Campinas", "São Bernardo do Campo"],
      "Rio de Janeiro": ["Rio de Janeiro", "Duque de Caxias"],
      "Minas Gerais": ["Belo Horizonte", "Betim"]
    }
  },
  "Mexico": {
    "code": "MX",
    "flag": "🇲🇽",
    "dial": "+52",
    "states": {
      "Nuevo León": ["Monterrey", "Apodaca"],
      "Mexico City": ["Mexico City"],
      "Jalisco": ["Guadalajara", "Zapopan"],
      "Querétaro": ["Querétaro"]
    }
  },
  "Indonesia": {
    "code": "ID",
    "flag": "🇮🇩",
    "dial": "+62",
    "states": {
      "West Java": ["Bekasi", "Cikarang", "Karawang", "Bandung"],
      "DKI Jakarta": ["Jakarta", "Pulo Gadung"],
      "East Java": ["Surabaya", "Sidoarjo"]
    }
  },
  "Poland": {
    "code": "PL",
    "flag": "🇵🇱",
    "dial": "+48",
    "states": {
      "Silesia": ["Katowice", "Gliwice", "Sosnowiec"],
      "Masovia": ["Warsaw", "Radom"],
      "Lower Silesia": ["Wroclaw"]
    }
  },
  "Sweden": {
    "code": "SE",
    "flag": "🇸🇪",
    "dial": "+46",
    "states": {
      "Stockholm": ["Stockholm", "Södertälje"],
      "Västra Götaland": ["Gothenburg", "Borås"],
      "Skåne": ["Malmö", "Helsingborg"]
    }
  },
  "Netherlands": {
    "code": "NL",
    "flag": "🇳🇱",
    "dial": "+31",
    "states": {
      "North Holland": ["Amsterdam", "Haarlem"],
      "South Holland": ["Rotterdam", "The Hague"],
      "North Brabant": ["Eindhoven", "Tilburg"]
    }
  },
  "Other Worldwide": {
    "code": "INT",
    "flag": "🌐",
    "dial": "+",
    "states": {
      "International / Other Region": ["Industrial Hub / Capital", "Other City"]
    }
  }
};

const PRESET_COUNTRY = "<?php echo addslashes($_POST['country'] ?? ''); ?>";
const PRESET_STATE = "<?php echo addslashes($_POST['state'] ?? ''); ?>";
const PRESET_CITY = "<?php echo addslashes($_POST['city'] ?? ''); ?>";

const countrySelect = document.getElementById('reg_country');
const stateSelect = document.getElementById('reg_state');
const citySelect = document.getElementById('reg_city');
const customCityBox = document.getElementById('custom_city_box');
const customCityInput = document.getElementById('reg_city_custom');
const phoneInput = document.getElementById('reg_phone');
const previewText = document.getElementById('location_preview_text');

const ALL_WORLD_COUNTRIES = [
  "India", "Japan", "Germany", "United States", "China", "South Korea", "Taiwan", "Italy", "United Kingdom", "Switzerland", "Singapore", "United Arab Emirates", "Vietnam", "Malaysia", "Turkey", "Spain", "France", "Canada", "Australia", "Saudi Arabia", "Brazil", "Mexico", "Indonesia", "Poland", "Sweden", "Netherlands",
  "Afghanistan", "Albania", "Algeria", "Andorra", "Angola", "Antigua and Barbuda", "Argentina", "Armenia", "Austria", "Azerbaijan", "Bahamas", "Bahrain", "Bangladesh", "Barbados", "Belarus", "Belgium", "Belize", "Benin", "Bhutan", "Bolivia", "Bosnia and Herzegovina", "Botswana", "Brunei", "Bulgaria", "Burkina Faso", "Burundi", "Cambodia", "Cameroon", "Cape Verde", "Central African Republic", "Chad", "Chile", "Colombia", "Comoros", "Congo", "Costa Rica", "Croatia", "Cuba", "Cyprus", "Czech Republic", "Denmark", "Djibouti", "Dominica", "Dominican Republic", "Ecuador", "Egypt", "El Salvador", "Equatorial Guinea", "Eritrea", "Estonia", "Eswatini", "Ethiopia", "Fiji", "Finland", "Gabon", "Gambia", "Georgia", "Ghana", "Greece", "Grenada", "Guatemala", "Guinea", "Guyana", "Haiti", "Honduras", "Hungary", "Iceland", "Iran", "Iraq", "Ireland", "Israel", "Ivory Coast", "Jamaica", "Jordan", "Kazakhstan", "Kenya", "Kiribati", "Kuwait", "Kyrgyzstan", "Laos", "Latvia", "Lebanon", "Lesotho", "Liberia", "Libya", "Liechtenstein", "Lithuania", "Luxembourg", "Madagascar", "Malawi", "Maldives", "Mali", "Malta", "Mauritania", "Mauritius", "Moldova", "Monaco", "Mongolia", "Montenegro", "Morocco", "Mozambique", "Myanmar", "Namibia", "Nauru", "Nepal", "New Zealand", "Nicaragua", "Niger", "Nigeria", "North Macedonia", "Norway", "Oman", "Pakistan", "Palau", "Panama", "Papua New Guinea", "Paraguay", "Peru", "Philippines", "Portugal", "Qatar", "Romania", "Russia", "Rwanda", "Saint Kitts and Nevis", "Saint Lucia", "Samoa", "San Marino", "Senegal", "Serbia", "Seychelles", "Sierra Leone", "Slovakia", "Slovenia", "Solomon Islands", "Somalia", "South Africa", "South Sudan", "Sri Lanka", "Sudan", "Suriname", "Syria", "Tajikistan", "Tanzania", "Thailand", "Togo", "Tonga", "Trinidad and Tobago", "Tunisia", "Turkmenistan", "Tuvalu", "Uganda", "Ukraine", "Uruguay", "Uzbekistan", "Vanuatu", "Vatican City", "Venezuela", "Yemen", "Zambia", "Zimbabwe", "Other Worldwide"
];

// Initialize all countries into LOCATION_DATA
ALL_WORLD_COUNTRIES.forEach(c => {
  if (!LOCATION_DATA[c]) {
    LOCATION_DATA[c] = {
      code: c.slice(0, 2).toUpperCase(),
      flag: "🌐",
      dial: "+",
      states: {
        "Main Industrial Region": ["Capital City", "Industrial Corridor", "Port District"],
        "Other State / Province": ["General Industrial Area"]
      }
    };
  }
});

function populateCountries() {
  countrySelect.innerHTML = '';
  ALL_WORLD_COUNTRIES.forEach(country => {
    if (LOCATION_DATA[country]) {
      const opt = document.createElement('option');
      opt.value = country;
      opt.textContent = `${LOCATION_DATA[country].flag} ${country}`;
      countrySelect.appendChild(opt);
    }
  });
}

function updateStates(selectedState = null) {
  const country = countrySelect.value;
  stateSelect.innerHTML = '';
  if (!LOCATION_DATA[country] || !LOCATION_DATA[country].states) {
    const opt = document.createElement('option');
    opt.value = "General Industrial Area";
    opt.textContent = "General Industrial Area";
    stateSelect.appendChild(opt);
    updateCities();
    return;
  }

  const states = LOCATION_DATA[country].states;
  for (const st in states) {
    const opt = document.createElement('option');
    opt.value = st;
    opt.textContent = st;
    stateSelect.appendChild(opt);
  }

  if (selectedState && states[selectedState]) {
    stateSelect.value = selectedState;
  } else {
    // If India, default state to Tamil Nadu
    if (country === 'India' && states['Tamil Nadu']) {
      stateSelect.value = 'Tamil Nadu';
    } else {
      stateSelect.selectedIndex = 0;
    }
  }

  updateCities();
}

function updateCities(selectedCity = null) {
  const country = countrySelect.value;
  const state = stateSelect.value;
  citySelect.innerHTML = '';

  const cities = (LOCATION_DATA[country] && LOCATION_DATA[country].states && LOCATION_DATA[country].states[state]) 
    ? LOCATION_DATA[country].states[state] 
    : ["Industrial Hub", "Capital Region"];

  cities.forEach(city => {
    const opt = document.createElement('option');
    opt.value = city;
    opt.textContent = city;
    citySelect.appendChild(opt);
  });

  // Always append custom city option
  const otherOpt = document.createElement('option');
  otherOpt.value = "OTHER";
  otherOpt.textContent = "+ Other / Type Custom City";
  citySelect.appendChild(otherOpt);

  if (selectedCity && (cities.includes(selectedCity) || selectedCity === 'OTHER')) {
    citySelect.value = selectedCity;
  } else {
    // If Tamil Nadu, default city to Madurai
    if (state === 'Tamil Nadu' && cities.includes('Madurai')) {
      citySelect.value = 'Madurai';
    } else {
      citySelect.selectedIndex = 0;
    }
  }

  handleCityChange();
}

function handleCityChange() {
  if (citySelect.value === 'OTHER') {
    customCityBox.classList.remove('hidden');
    customCityInput.focus();
  } else {
    customCityBox.classList.add('hidden');
  }
  updatePreview();
}

function updatePreview() {
  const country = countrySelect.value;
  const state = stateSelect.value;
  let city = citySelect.value;
  if (city === 'OTHER') {
    city = customCityInput.value.trim() || 'Custom City';
  }
  previewText.innerHTML = `Selected: <strong>${country}</strong> &bull; <strong>${state}</strong> &bull; <strong class="text-joule-brightblue">${city}</strong>`;
  
  // Auto-fill phone prefix if empty
  if (phoneInput && LOCATION_DATA[country]) {
    const dial = LOCATION_DATA[country].dial;
    if (!phoneInput.value || phoneInput.value.trim() === '' || phoneInput.value.match(/^\+\d{1,4}\s*$/)) {
      phoneInput.value = dial + ' ';
    }
  }
}

// Automatic Location Detection
function autoDetectLocation(isUserClick = false) {
  try {
    const tz = Intl.DateTimeFormat().resolvedOptions().timeZone || '';
    let matchedCountry = 'India';
    let matchedState = 'Tamil Nadu';
    let matchedCity = 'Madurai';

    if (tz.includes('Calcutta') || tz.includes('Kolkata') || navigator.language === 'en-IN' || navigator.language === 'ta-IN') {
      matchedCountry = 'India';
      matchedState = 'Tamil Nadu';
      matchedCity = 'Madurai';
    } else if (tz.includes('Tokyo')) {
      matchedCountry = 'Japan';
      matchedState = 'Aichi Prefecture';
      matchedCity = 'Nagoya';
    } else if (tz.includes('Berlin') || tz.includes('Munich')) {
      matchedCountry = 'Germany';
      matchedState = 'Bavaria';
      matchedCity = 'Munich';
    } else if (tz.includes('Chicago') || tz.includes('America')) {
      matchedCountry = 'United States';
      matchedState = 'Illinois';
      matchedCity = 'Chicago';
    } else if (tz.includes('Shanghai') || tz.includes('Chongqing')) {
      matchedCountry = 'China';
      matchedState = 'Shanghai';
      matchedCity = 'Shanghai';
    } else if (tz.includes('Seoul')) {
      matchedCountry = 'South Korea';
      matchedState = 'Seoul';
      matchedCity = 'Seoul';
    } else if (tz.includes('Taipei')) {
      matchedCountry = 'Taiwan';
      matchedState = 'Taichung';
      matchedCity = 'Taichung';
    } else if (tz.includes('London')) {
      matchedCountry = 'United Kingdom';
      matchedState = 'West Midlands';
      matchedCity = 'Birmingham';
    } else if (tz.includes('Rome')) {
      matchedCountry = 'Italy';
      matchedState = 'Lombardy';
      matchedCity = 'Milan';
    } else if (tz.includes('Zurich')) {
      matchedCountry = 'Switzerland';
      matchedState = 'Canton of Bern';
      matchedCity = 'Moutier';
    } else if (tz.includes('Singapore')) {
      matchedCountry = 'Singapore';
      matchedState = 'Western Region';
      matchedCity = 'Jurong Industrial Estate';
    } else if (tz.includes('Dubai')) {
      matchedCountry = 'United Arab Emirates';
      matchedState = 'Dubai';
      matchedCity = 'Dubai';
    }

    countrySelect.value = matchedCountry;
    updateStates(matchedState);
    updateCities(matchedCity);
    updatePreview();

    if (isUserClick) {
      previewText.innerHTML = `Auto-Detected: <strong>${matchedCountry}</strong> &bull; <strong>${matchedState}</strong> &bull; <strong class="text-emerald-600">${matchedCity}</strong>`;
    }
  } catch (e) {
    console.error("Auto-detect fallback:", e);
  }
}

// Event Listeners
countrySelect.addEventListener('change', () => {
  updateStates();
  updatePreview();
});

stateSelect.addEventListener('change', () => {
  updateCities();
  updatePreview();
});

citySelect.addEventListener('change', handleCityChange);
customCityInput.addEventListener('input', updatePreview);

// Initialize on Load
document.addEventListener('DOMContentLoaded', () => {
  populateCountries();
  if (PRESET_COUNTRY && LOCATION_DATA[PRESET_COUNTRY]) {
    countrySelect.value = PRESET_COUNTRY;
    updateStates(PRESET_STATE);
    updateCities(PRESET_CITY);
  } else {
    autoDetectLocation(false);
  }
  updatePreview();
});
</script>

<?php
require_once __DIR__ . '/includes/footer.php';
?>
