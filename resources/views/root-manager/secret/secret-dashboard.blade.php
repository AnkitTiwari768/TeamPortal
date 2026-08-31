@extends('components.admin.content-layout')
@section('card-content')

<div class="card-body">
    <!-- Dashboard Header with Logo and Logout -->
    <div class="dashboard-header mb-4 d-flex justify-content-between align-items-center">
        <div>
            <h2><i class="fa fa-tachometer-alt me-2"></i> Secret Dashboard</h2>
            <p class="text-muted mb-0">Welcome to your administrative control panel</p>
        </div>
        <div class="header-actions d-flex gap-3 align-items-center">
           
            
            <!-- Logout Icon -->
            <div class="logout-icon" onclick="handleLogout()" style="cursor: pointer;">
                <i class="fa fa-sign-out-alt"></i>
               <button onclick="logoutSecret()">Logout</button>
            </div>
            
           
        </div>
    </div>

    <!-- Menu Grid - 4 boxes per row -->
    <div class="row g-4">
        <!-- MSE Card -->
       <div class="col-md-6 col-lg-3">
            <a href="{{ route('bppid-update') }}"    target="_blank" class="text-decoration-none">
                <div class="menu-card menu-card-1">
                    <div class="menu-card-icon">
                        <i class="fa fa-building"></i>
                    </div>
                    <div class="menu-card-title">BPP ID Mapping</div>
                   
                </div>
            </a>
        </div>

        <!-- View Claim Section Card -->
        <div class="col-md-6 col-lg-3">
            <div class="menu-card menu-card-2">
                <a href="{{ url('state-wise-msme-list') }}" target="_blank" class="text-decoration-none">
                <div class="menu-card-icon">
                    <i class="fa fa-file-text"></i>
                </div>
                <div class="menu-card-title">State Wise Msme</div>
              
            </div>
</a>
        </div>

        <!-- User Management Card -->
        <div class="col-md-6 col-lg-3">
            <div class="menu-card menu-card-3">
                <a href="{{ url('msme-list') }}" target="_blank" class="text-decoration-none">
                <div class="menu-card-icon">
                    <i class="fa fa-users"></i>
                </div>
                <div class="menu-card-title">SNP Wise MSME Count</div>
            
            </div>
</a>
        </div>

        <!-- Workshop Management Card -->
        <div class="col-md-6 col-lg-3">
            <div class="menu-card menu-card-4">
                <a href="{{ url('my-list') }}" target="_blank" class="text-decoration-none">
                <div class="menu-card-icon">
                    <i class="fa fa-wrench"></i>
                </div>
                <div class="menu-card-title">MSME with SNP List</div>
           
            </div>
</a>
        </div>

        <!-- MIS Report Card -->
        <div class="col-md-6 col-lg-3">
            <div class="menu-card menu-card-5">
                <a href="{{ url('city-msme-list') }}" target="_blank" class="text-decoration-none">
                <div class="menu-card-icon">
                    <i class="fa fa-chart-line"></i>
                </div>
                <div class="menu-card-title">City MSME Registration List</div>
                
            </div>
</a>
        </div>

        <!-- PMV List Card -->
        <div class="col-md-6 col-lg-3">
            <div class="menu-card menu-card-6">
                <a href="{{ url('rollback-batch') }}" target="_blank" class="text-decoration-none">
                <div class="menu-card-icon">
                    <i class="fa fa-list"></i>
                </div>
                <div class="menu-card-title">Rollback Batch</div>
                
            </div>
</a>
        </div>

        <!-- Settings Card -->
        <div class="col-md-6 col-lg-3">
            <div class="menu-card menu-card-7">
                <a href="{{ url('state-count-list') }}" target="_blank" class="text-decoration-none">
                <div class="menu-card-icon">
                    <i class="fa fa-cog"></i>
                </div>
                <div class="menu-card-title">State Count</div>
              
            </div>
</a>
        </div>

        <!-- Reports Card -->
        <div class="col-md-6 col-lg-3">
            <div class="menu-card menu-card-8">
                <a href="{{ url('user-bypass') }}" target="_blank" class="text-decoration-none">
                <div class="menu-card-icon">
                    <i class="fa fa-chart-bar"></i>
                </div>
                <div class="menu-card-title">By Paa Users</div>
        </a>
            </div>
        </div>

           <div class="col-md-6 col-lg-3">
            <a href="{{ route('$2y$10$M/communication-center') }}"    target="_blank" class="text-decoration-none">
                <div class="menu-card menu-card-1">
                    <div class="menu-card-icon">
                        <i class="fa fa-building"></i>
                    </div>
                    <div class="menu-card-title">Email & SMS Test</div>
                   
                </div>
            </a>
        </div>
    </div>
</div>

<style>
/* Body Background */
.card-body {
    background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
    padding: 30px;
    border-radius: 20px;
}

/* Dashboard Header */
.dashboard-header {
    margin-bottom: 30px;
    background: rgb(201 201 201 / 90%);
    padding: 20px;
    border-radius: 15px;
    backdrop-filter: blur(10px);
}

.dashboard-header h2 {
    font-size: 28px;
    font-weight: 600;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
    margin: 0;
    padding-bottom: 10px;
}

.header-actions {
    display: flex;
    gap: 20px;
    align-items: center;
}

/* Cart Icon Styles */
.cart-icon {
    position: relative;
    font-size: 28px;
    color: #667eea;
    transition: all 0.3s ease;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    width: 50px;
    height: 50px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
}

.cart-icon:hover {
    transform: scale(1.1);
    box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
}

.cart-badge {
    position: absolute;
    top: -5px;
    right: -5px;
    background: #ff6b6b;
    color: white;
    border-radius: 50%;
    width: 20px;
    height: 20px;
    font-size: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: bold;
}

/* Logout Icon Styles */
.logout-icon {
    display: flex;
    align-items: center;
    gap: 8px;
    background: linear-gradient(135deg, #ff6b6b 0%, #ee5a24 100%);
    padding: 10px 20px;
    border-radius: 50px;
    color: white;
    transition: all 0.3s ease;
}

.logout-icon i {
    font-size: 20px;
}

.logout-text {
    font-size: 14px;
    font-weight: 500;
}

.logout-icon:hover {
    transform: translateX(5px);
    box-shadow: 0 5px 15px rgba(238, 90, 36, 0.4);
}

/* Logo Section */
.logo-section {
    position: relative;
    transition: all 0.3s ease;
}

.dashboard-logo {
    width: 60px;
    height: 60px;
    border-radius: 50%;
    object-fit: cover;
    border: 2px solid #667eea;
    padding: 3px;
    transition: all 0.3s ease;
}

.logo-section:hover .dashboard-logo {
    transform: scale(1.05);
    box-shadow: 0 5px 15px rgba(102, 126, 234, 0.3);
}

.secret-hint {
    position: absolute;
    bottom: -5px;
    right: -5px;
    background: #ff6b6b;
    border-radius: 50%;
    width: 20px;
    height: 20px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 10px;
    animation: pulse 2s infinite;
}

@keyframes pulse {
    0% {
        transform: scale(1);
        opacity: 1;
    }
    50% {
        transform: scale(1.2);
        opacity: 0.7;
    }
    100% {
        transform: scale(1);
        opacity: 1;
    }
}

/* Different colors for each card */
.menu-card {
    background: rgba(255, 255, 255, 0.95);
    border-radius: 15px;
    padding: 25px 15px;
    text-align: center;
    cursor: pointer;
    transition: all 0.3s ease;
    box-shadow: 0 5px 15px rgba(0,0,0,0.1);
    border: 1px solid rgba(255,255,255,0.2);
    height: 100%;
    backdrop-filter: blur(5px);
}

.menu-card-1 .menu-card-icon {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}

.menu-card-2 .menu-card-icon {
    background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
}

.menu-card-3 .menu-card-icon {
    background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
}

.menu-card-4 .menu-card-icon {
    background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);
}

.menu-card-5 .menu-card-icon {
    background: linear-gradient(135deg, #fa709a 0%, #fee140 100%);
}

.menu-card-6 .menu-card-icon {
    background: linear-gradient(135deg, #a18cd1 0%, #fbc2eb 100%);
}

.menu-card-7 .menu-card-icon {
    background: linear-gradient(135deg, #ff9a9e 0%, #fecfef 100%);
}

.menu-card-8 .menu-card-icon {
    background: linear-gradient(135deg, #ffecd2 0%, #fcb69f 100%);
}

.menu-card:hover {
    transform: translateY(-8px);
    box-shadow: 0 15px 35px rgba(0,0,0,0.2);
    background: white;
}

.menu-card-icon {
    width: 70px;
    height: 70px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 20px;
    transition: all 0.3s ease;
}

.menu-card:hover .menu-card-icon {
    transform: scale(1.1) rotate(5deg);
}

.menu-card-icon i {
    font-size: 32px;
    color: #fff;
}

.menu-card-title {
    font-size: 18px;
    font-weight: 600;
    color: #2c3e50;
    margin-bottom: 10px;
}

.menu-card-desc {
    font-size: 13px;
    color: #7f8c8d;
}

/* Responsive */
@media (max-width: 992px) {
    .col-lg-3 {
        width: 50%;
    }
}

@media (max-width: 768px) {
    .menu-card {
        padding: 20px 15px;
    }
    
    .menu-card-icon {
        width: 55px;
        height: 55px;
    }
    
    .menu-card-icon i {
        font-size: 24px;
    }
    
    .menu-card-title {
        font-size: 16px;
    }
    
    .dashboard-header {
        flex-direction: column;
        text-align: center;
    }
    
    .header-actions {
        margin-top: 15px;
        justify-content: center;
    }
    
    .logout-text {
        display: none;
    }
    
    .logout-icon {
        padding: 10px;
    }
}
</style>

<script>
function handleMenuClick(menuName) {
    console.log('Clicked on:', menuName);
    // Add your navigation logic here
    alert('Navigating to ' + menuName);
}



function handleLogout() {
    // Logout functionality
    if(confirm('Are you sure you want to logout?')) {
        // Add your logout logic here
        window.location.href = '/logout';
        console.log('Logging out...');
    }
}
</script>

<script>
function handleLogout() {

    if (!confirm('Are you sure you want to logout?')) {
        return;
    }

    fetch("{{ route('secret.logout') }}", {
        method: "POST",
        headers: {
            "X-CSRF-TOKEN": "{{ csrf_token() }}",
            "Content-Type": "application/json"
        }
    })
    .then(response => response.json())
    .then(data => {
        window.location.href = "{{ route('secret.page') }}";
    })
    .catch(error => {
        console.error(error);
    });
}
</script>
@endsection