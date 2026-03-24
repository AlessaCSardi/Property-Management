<?php
// VECTAFLOW RESIDENTIAL - MULTI-TENANT PROPTECH v1.0
// Developed by Alessa Claro
session_start();

// Mock User Session - In a real app, this comes from a Login page
$userRole = $_GET['role'] ?? 'landlord'; 

// --- DATA PERSISTENCE (The Property Database) ---
if (!isset($_SESSION['properties'])) {
    $_SESSION['properties'] = [
        ['id' => '101', 'address' => '7250 S Maryland Pkwy', 'status' => 'Occupied', 'rent' => 1800],
        ['id' => '202', 'address' => '4505 S Maryland Pkwy', 'status' => 'Vacant', 'rent' => 2100]
    ];
}
if (!isset($_SESSION['maintenance'])) $_SESSION['maintenance'] = [];

// --- OPERATIONS LOGIC ---
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // 1. Tenant Submits Request
    if (isset($_POST['submit_request'])) {
        $_SESSION['maintenance'][] = [
            'id' => uniqid(),
            'property' => $_POST['property'],
            'issue' => $_POST['issue'],
            'status' => 'Pending',
            'date' => date('Y-m-d')
        ];
    }
    // 2. Landlord Assigns Contractor
    if (isset($_POST['assign_work'])) {
        foreach($_SESSION['maintenance'] as &$req) {
            if($req['id'] == $_POST['req_id']) { $req['status'] = 'Assigned'; $req['contractor'] = $_POST['contractor']; }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>VectaFlow | <?php echo ucfirst($userRole); ?> Portal</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; background: #f8fafc; color: #1e293b; }
        .sidebar { background: #0f172a; color: white; }
        .card { background: white; border: 1px solid #e2e8f0; border-radius: 1rem; box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1); }
        .badge-pending { background: #fef3c7; color: #92400e; }
        .badge-assigned { background: #dcfce7; color: #166534; }
    </style>
</head>
<body class="min-h-screen flex">

    <aside class="w-64 sidebar hidden md:flex flex-col p-8 fixed h-full">
        <div class="mb-12">
            <h1 class="text-2xl font-black tracking-tighter italic">VECTA<span class="text-blue-500">FLOW</span></h1>
            <p class="text-[10px] uppercase tracking-widest text-slate-500 font-bold">Residential OS</p>
        </div>
        
        <nav class="space-y-6 flex-1 text-sm font-bold">
            <a href="?role=landlord" class="block hover:text-blue-400 <?php echo $userRole == 'landlord' ? 'text-blue-500' : ''; ?>">Landlord View</a>
            <a href="?role=tenant" class="block hover:text-blue-400 <?php echo $userRole == 'tenant' ? 'text-blue-500' : ''; ?>">Tenant View</a>
            <a href="?role=contractor" class="block hover:text-blue-400 <?php echo $userRole == 'contractor' ? 'text-blue-500' : ''; ?>">Contractor View</a>
        </nav>

        <div class="pt-8 border-t border-slate-800 text-[10px] text-slate-500 uppercase">
            Developed by Alessa Claro
        </div>
    </aside>

    <main class="flex-1 md:ml-64 p-8 md:p-12">
        
        <header class="flex justify-between items-center mb-12">
            <h2 class="text-3xl font-black">Welcome back, <span class="text-blue-600"><?php echo ucfirst($userRole); ?></span></h2>
            <div class="text-xs font-bold bg-slate-200 px-4 py-2 rounded-full uppercase">System Status: Active</div>
        </header>

        <?php if ($userRole == 'landlord'): ?>
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 mb-12">
                <div class="card p-6">
                    <p class="text-slate-500 text-xs font-bold uppercase mb-2">Total Portfolio Value</p>
                    <p class="text-3xl font-black">$3.9M</p>
                </div>
                <div class="card p-6">
                    <p class="text-slate-500 text-xs font-bold uppercase mb-2">Occupancy Rate</p>
                    <p class="text-3xl font-black">94.2%</p>
                </div>
                <div class="card p-6">
                    <p class="text-slate-500 text-xs font-bold uppercase mb-2">Pending Repairs</p>
                    <p class="text-3xl font-black text-orange-500"><?php echo count(array_filter($_SESSION['maintenance'], fn($m) => $m['status'] == 'Pending')); ?></p>
                </div>
            </div>

            <div class="card p-8">
                <h3 class="text-xl font-black mb-6">Maintenance Dispatch Queue</h3>
                <table class="w-full text-left">
                    <thead class="text-slate-400 text-xs uppercase border-b">
                        <tr><th class="pb-4">Property</th><th class="pb-4">Issue</th><th class="pb-4">Status</th><th class="pb-4">Action</th></tr>
                    </thead>
                    <tbody>
                        <?php foreach($_SESSION['maintenance'] as $req): ?>
                            <tr class="border-b last:border-0">
                                <td class="py-4 font-bold"><?php echo $req['property']; ?></td>
                                <td class="py-4 italic text-sm text-slate-600"><?php echo $req['issue']; ?></td>
                                <td class="py-4">
                                    <span class="px-3 py-1 rounded-full text-[10px] font-black uppercase <?php echo $req['status'] == 'Pending' ? 'badge-pending' : 'badge-assigned'; ?>">
                                        <?php echo $req['status']; ?>
                                    </span>
                                </td>
                                <td class="py-4">
                                    <?php if($req['status'] == 'Pending'): ?>
                                        <form method="POST" class="flex gap-2">
                                            <input type="hidden" name="req_id" value="<?php echo $req['id']; ?>">
                                            <input type="text" name="contractor" placeholder="Contractor Name" class="text-xs border p-1 rounded">
                                            <button type="submit" name="assign_work" class="bg-blue-600 text-white text-[10px] px-3 py-1 rounded font-bold">Assign</button>
                                        </form>
                                    <?php else: ?>
                                        <span class="text-[10px] font-bold text-slate-400">Handled by <?php echo $req['contractor']; ?></span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

        <?php elseif ($userRole == 'tenant'): ?>
            <div class="max-w-2xl">
                <div class="card p-8">
                    <h3 class="text-2xl font-black mb-6 italic">Submit Maintenance Request</h3>
                    <form method="POST" class="space-y-6">
                        <div>
                            <label class="block text-xs font-bold uppercase text-slate-500 mb-2">Select Property</label>
                            <select name="property" class="w-full border p-3 rounded-xl text-sm outline-none bg-slate-50">
                                <option>7250 S Maryland Pkwy #101</option>
                                <option>4505 S Maryland Pkwy #202</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-bold uppercase text-slate-500 mb-2">Describe the Issue</label>
                            <textarea name="issue" rows="4" class="w-full border p-4 rounded-xl text-sm outline-none" placeholder="e.g. Water leak in the kitchen..."></textarea>
                        </div>
                        <button type="submit" name="submit_request" class="w-full bg-blue-600 text-white font-black py-4 rounded-xl hover:bg-slate-900 transition shadow-xl">SUBMIT TO LANDLORD</button>
                    </form>
                </div>
            </div>

        <?php elseif ($userRole == 'contractor'): ?>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                <?php foreach($_SESSION['maintenance'] as $req): ?>
                    <?php if($req['status'] == 'Assigned'): ?>
                        <div class="card p-8 border-l-8 border-l-blue-500">
                            <div class="flex justify-between items-start mb-4">
                                <h4 class="font-black text-lg"><?php echo $req['property']; ?></h4>
                                <span class="text-[10px] font-bold text-slate-400">DATE: <?php echo $req['date']; ?></span>
                            </div>
                            <p class="text-slate-600 text-sm italic mb-6">"<?php echo $req['issue']; ?>"</p>
                            <button class="w-full bg-slate-100 text-slate-800 font-bold py-2 rounded uppercase text-xs">Mark as Completed</button>
                        </div>
                    <?php endif; ?>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

    </main>
</body>
</html>
