<?php
// Joule Machine Hub - Membership, Subscriptions, Payments & Billing Service
require_once __DIR__ . '/db.php';

/**
 * Returns available membership subscription plans
 */
function getMembershipPlans() {
    return [
        'free' => [
            'key'            => 'free',
            'name'           => 'Free Membership',
            'tagline'        => 'Ideal for small workshops & starter sellers',
            'price_inr'      => 0,
            'price_usd'      => 0,
            'billing_period' => 'Forever Free',
            'badge'          => 'Current Plan',
            'badge_color'    => 'bg-slate-100 text-slate-700 border-slate-300',
            'listing_limit'  => 2,
            'photo_limit'    => 5,
            'has_video'      => false,
            'featured_limit' => 0,
            'analytics_tier' => 'Basic',
            'features'       => [
                '2 machine listings',
                '5 photos per machine',
                'Basic enquiry management',
                'Direct buyer RFQ routing',
                'Standard search listing'
            ],
            'button_text'    => 'Current Plan',
            'popular'        => false
        ],
        'basic' => [
            'key'            => 'basic',
            'name'           => 'Basic Seller Plan',
            'tagline'        => 'For growing machine dealers & plant owners',
            'price_inr'      => 999,
            'price_usd'      => 15,
            'billing_period' => '/ month',
            'badge'          => 'Most Popular',
            'badge_color'    => 'bg-blue-100 text-blue-800 border-blue-300',
            'listing_limit'  => 10,
            'photo_limit'    => 10,
            'has_video'      => true,
            'featured_limit' => 2,
            'analytics_tier' => 'Standard',
            'features'       => [
                '10 machine listings',
                '10 photos per machine',
                'Machine working videos',
                '2 featured listings included',
                'Standard lead analytics',
                'Priority verified seller badge'
            ],
            'button_text'    => 'Upgrade to Basic',
            'popular'        => true
        ],
        'premium' => [
            'key'            => 'premium',
            'name'           => 'Premium Pro Plan',
            'tagline'        => 'Maximum exposure for industrial plant liquidations',
            'price_inr'      => 2499,
            'price_usd'      => 35,
            'billing_period' => '/ month',
            'badge'          => 'Best Value',
            'badge_color'    => 'bg-amber-100 text-amber-800 border-amber-300',
            'listing_limit'  => 50,
            'photo_limit'    => 15,
            'has_video'      => true,
            'featured_limit' => 10,
            'analytics_tier' => 'Advanced',
            'features'       => [
                '50 machine listings',
                '15 photos per machine',
                'Video & brochure PDF uploads',
                '10 featured listings included',
                'Advanced buyer lead analytics',
                'Top search rank placement',
                'Dedicated account manager'
            ],
            'button_text'    => 'Choose Premium',
            'popular'        => false
        ],
        'enterprise' => [
            'key'            => 'enterprise',
            'name'           => 'Enterprise Unlimited',
            'tagline'        => 'Custom solutions for large OEMs & auctioneers',
            'price_inr'      => 9999,
            'price_usd'      => 120,
            'billing_period' => '/ month',
            'badge'          => 'Custom',
            'badge_color'    => 'bg-purple-100 text-purple-800 border-purple-300',
            'listing_limit'  => 500,
            'photo_limit'    => 20,
            'has_video'      => true,
            'featured_limit' => 50,
            'analytics_tier' => 'Enterprise',
            'features'       => [
                'Unlimited machine inventory',
                'Up to 20 photos per unit',
                'Full video & CAD doc hosting',
                'Homepage brand showcase banner',
                'Direct CRM & webhook API feeds',
                'Instant phone lead SMS alerts'
            ],
            'button_text'    => 'Contact Enterprise',
            'popular'        => false
        ]
    ];
}

/**
 * Returns available Featured Machine promotional packages
 */
function getFeaturedPromotionPackages() {
    return [
        '7day' => [
            'key'          => '7day',
            'name'         => '7-Day Featured Listing',
            'days'         => 7,
            'price_inr'    => 499,
            'price_usd'    => 7,
            'description'  => 'Top placement in search results and category page for 7 days.'
        ],
        '15day' => [
            'key'          => '15day',
            'name'         => '15-Day Featured Listing',
            'days'         => 15,
            'price_inr'    => 899,
            'price_usd'    => 12,
            'description'  => 'Prominent featured badge + priority search ranking for 15 days.'
        ],
        '30day' => [
            'key'          => '30day',
            'name'         => '30-Day Featured Listing',
            'days'         => 30,
            'price_inr'    => 1499,
            'price_usd'    => 20,
            'description'  => 'Maximum visibility for an entire month across all relevant buyer searches.'
        ],
        'homepage' => [
            'key'          => 'homepage',
            'name'         => 'Homepage Spotlight Promotion',
            'days'         => 30,
            'price_inr'    => 2499,
            'price_usd'    => 35,
            'description'  => 'Guaranteed featured card on Joule Machine Hub homepage for 30 days.'
        ]
    ];
}

/**
 * Get active user subscription with plan details
 */
function getUserSubscription($userId) {
    $plans = getMembershipPlans();
    $subFile = __DIR__ . '/../data/subscriptions.json';
    $subs = file_exists($subFile) ? (json_decode(file_get_contents($subFile), true) ?: []) : [];

    $userSub = null;
    foreach ($subs as $s) {
        if (($s['user_id'] ?? '') === $userId) {
            $userSub = $s;
            break;
        }
    }

    // Default to free if none active
    if (!$userSub) {
        $userSub = [
            'user_id'    => $userId,
            'plan_key'   => 'free',
            'plan_name'  => 'Free Membership',
            'amount'     => 0,
            'currency'   => 'INR',
            'status'     => 'active',
            'start_date' => date('Y-m-d H:i:s'),
            'end_date'   => date('Y-m-d H:i:s', strtotime('+10 years'))
        ];
    }

    $planKey = $userSub['plan_key'] ?? 'free';
    $planMeta = $plans[$planKey] ?? $plans['free'];

    return array_merge($userSub, [
        'plan_details'  => $planMeta,
        'listing_limit' => $planMeta['listing_limit'],
        'photo_limit'   => $planMeta['photo_limit'],
        'has_video'     => $planMeta['has_video'],
        'is_active'     => ($userSub['status'] === 'active' && strtotime($userSub['end_date']) > time())
    ]);
}

/**
 * Activate or update a user subscription
 */
function activateUserSubscription($userId, $planKey, $amount, $currency = 'INR', $gateway = 'razorpay', $txnId = null) {
    $plans = getMembershipPlans();
    if (!isset($plans[$planKey])) {
        return ['success' => false, 'message' => 'Invalid plan selected.'];
    }

    $plan = $plans[$planKey];
    $txnId = $txnId ?: 'TXN-' . strtoupper(substr(md5(uniqid(rand(), true)), 0, 12));
    $startDate = date('Y-m-d H:i:s');
    $endDate = date('Y-m-d H:i:s', strtotime('+30 days'));

    $subRecord = [
        'user_id'    => $userId,
        'plan_key'   => $planKey,
        'plan_name'  => $plan['name'],
        'amount'     => $amount,
        'currency'   => $currency,
        'gateway'    => $gateway,
        'txn_id'     => $txnId,
        'status'     => 'active',
        'start_date' => $startDate,
        'end_date'   => $endDate,
        'updated_at' => date('Y-m-d H:i:s')
    ];

    // Save to subscriptions.json
    $subFile = __DIR__ . '/../data/subscriptions.json';
    $subs = file_exists($subFile) ? (json_decode(file_get_contents($subFile), true) ?: []) : [];
    $updated = false;
    foreach ($subs as &$s) {
        if (($s['user_id'] ?? '') === $userId) {
            $s = $subRecord;
            $updated = true;
            break;
        }
    }
    if (!$updated) {
        $subs[] = $subRecord;
    }
    file_put_contents($subFile, json_encode($subs, JSON_PRETTY_PRINT));

    // Update user record in session & users.json
    if (isset($_SESSION['user'])) {
        $_SESSION['user']['plan'] = $planKey;
        $_SESSION['user']['plan_expiry'] = $endDate;
    }

    // Create payment transaction & invoice record
    createPaymentRecord(
        $userId,
        'membership',
        $plan['name'],
        $amount,
        $currency,
        $gateway,
        'paid',
        $txnId
    );

    return ['success' => true, 'message' => "Successfully upgraded to {$plan['name']}!", 'subscription' => $subRecord];
}

/**
 * Logs a payment and generates invoice
 */
function createPaymentRecord($userId, $type, $title, $amount, $currency = 'INR', $gateway = 'razorpay', $status = 'paid', $txnId = null) {
    $invoiceId = 'INV-' . date('Ymd') . '-' . strtoupper(substr(md5(uniqid(rand(), true)), 0, 6));
    $txnId = $txnId ?: 'TXN-' . strtoupper(substr(md5(uniqid(rand(), true)), 0, 10));

    $record = [
        'invoice_id' => $invoiceId,
        'user_id'    => $userId,
        'title'      => $title,
        'type'       => $type,
        'amount'     => $amount,
        'currency'   => $currency,
        'gateway'    => $gateway,
        'txn_id'     => $txnId,
        'status'     => $status,
        'date'       => date('Y-m-d H:i:s'),
        'created_at' => date('Y-m-d H:i:s')
    ];

    $payFile = __DIR__ . '/../data/payments.json';
    $payments = file_exists($payFile) ? (json_decode(file_get_contents($payFile), true) ?: []) : [];
    array_unshift($payments, $record);
    file_put_contents($payFile, json_encode($payments, JSON_PRETTY_PRINT));

    return $record;
}

/**
 * Returns all payment history for a user
 */
function getUserPayments($userId) {
    $payFile = __DIR__ . '/../data/payments.json';
    if (!file_exists($payFile)) return [];
    $payments = json_decode(file_get_contents($payFile), true) ?: [];
    
    $userPayments = [];
    foreach ($payments as $p) {
        if (($p['user_id'] ?? '') === $userId) {
            $userPayments[] = $p;
        }
    }
    return $userPayments;
}

/**
 * Get single invoice details
 */
function getInvoiceDetails($invoiceId, $userId) {
    $payments = getUserPayments($userId);
    foreach ($payments as $p) {
        if (($p['invoice_id'] ?? '') === $invoiceId) {
            return $p;
        }
    }
    return null;
}

/**
 * Calculates user listing statistics for dashboard cards
 */
function getUserListingStats($userId) {
    require_once __DIR__ . '/seller.php';
    $products = getSellerProductsList($userId);

    $total = count($products);
    $approved = 0;
    $pending = 0;
    $rejected = 0;
    $draft = 0;
    $sold = 0;

    foreach ($products as $p) {
        $st = strtolower($p['status'] ?? 'pending');
        if (in_array($st, ['approved', 'active', 'published'])) {
            $approved++;
        } elseif (in_array($st, ['pending', 'pending_approval'])) {
            $pending++;
        } elseif ($st === 'rejected') {
            $rejected++;
        } elseif ($st === 'draft') {
            $draft++;
        } elseif ($st === 'sold') {
            $sold++;
        } else {
            $pending++;
        }
    }

    // Get count of enquiries
    $leadsFile = __DIR__ . '/../data/leads.json';
    $leads = file_exists($leadsFile) ? (json_decode(file_get_contents($leadsFile), true) ?: []) : [];
    $totalEnquiries = 0;
    foreach ($leads as $l) {
        // Count all inquiries if seller, or inquiries matching seller's email/name/machine
        if (!empty($userId)) {
            $totalEnquiries++;
        }
    }

    $sub = getUserSubscription($userId);

    return [
        'total'           => $total,
        'approved'        => $approved,
        'pending'         => $pending,
        'rejected'        => $rejected,
        'draft'           => $draft,
        'sold'            => $sold,
        'total_enquiries' => $totalEnquiries,
        'listing_limit'   => $sub['listing_limit'],
        'can_upload'      => ($total < $sub['listing_limit']),
        'remaining'       => max(0, $sub['listing_limit'] - $total),
        'plan_name'       => $sub['plan_name']
    ];
}
