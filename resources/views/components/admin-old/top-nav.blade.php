@php

    $user = auth()->user();
    
    $userFirstName = $user->first_name ?? __('Guest');
    $userProfilePicture = isset($user->profile_picture)
        ? asset("storage/app/public/users/{$user->profile_picture}")
        : asset('assets/img-new/user-icon.png');

    $organizationName = DB::table('team_snp_scheme')
                    ->where('user_id', $user->id)
                    ->value('organization_name');
@endphp


<nav class="sb-topnav navbar navbar-expand navbar-dark bg-dark">
    <!-- Navbar Brand-->
    <a class="navbar-brand ps-3" href="{{ url('dashboard') }}"> <img
            src="{{ asset('assets/ffo-admin/img/msme-team-logo.svg') }}"> </a>
    <!-- Sidebar Toggle-->
    <button class="btn btn-link btn-sm order-1 order-lg-0 me-4 me-lg-0" id="sidebarToggle" href="#!">
        <img src="{{ asset('assets/ffo-admin/img/menu-top.svg') }}">
    </button>
    <div class="header-user-name text-dark">
     <h5 class="text-dark fw-bold">
    {{ __('WELCOME') }} {{ ucwords($organizationName ?: $userFirstName) }}
</h5>
    </div>



    <!-- Navbar-->
    <ul class="navbar-nav ms-auto me-3 me-lg-4 user-dropdown d-flex align-items-center justify-content-center flex-wrap gap-2">

        <!--Notification Alert-->
    <!-- Notifications Dropdown -->
      <li>
    <div class="btn-group">
        <button type="button" class="noti-bell" id="notificationAlert" data-bs-toggle="dropdown">
            <i class="fa fa-bell-o"></i>
            <span class="badge bg-danger" id="notificationCount"></span>
        </button>

        <ul class="dropdown-menu dropdown-menu-end notification-data p-2" style="width: 320px; max-height: 400px; overflow-y: auto;">
            <li class="d-flex justify-content-between px-2 py-2 border-bottom">
                <strong>Notifications</strong>
                <a href="javascript:void(0);" id="markAllRead">Mark all</a>
            </li>
        </ul>
    </div>
</li>

            <!--Notification Alert End-->


        <li class="nav-item dropdown">
            <button type="button" class="noti-bell" id="navbarDropdown" data-bs-toggle="dropdown"
                data-bs-auto-close="outside" aria-expanded="false">
                <i class="bell fa fa-user-o"></i>
            </button>
            <a class="dropdown-toggle"></a>
            <!-- <a class="nav-link dropdown-toggle p-0" id="navbarDropdown" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                <img src="{{ $userProfilePicture }}">  -->
       <style>
    /* Remove hover and active background for the download certificate link */
    .dropdown-item.no-hover:hover,
    .dropdown-item.no-hover:focus,
    .dropdown-item.no-hover:active {
        background-color: transparent !important;
        color: inherit; /* Keeps text color consistent */
    }
    /* Optional: keep the default text color on hover/focus */
    .dropdown-item.no-hover:hover {
        color: #212529; /* or your theme's default text color */
    }
</style>

            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
                <li><a class="dropdown-item" href="{{ url('profile') }}">{{ __('My Profile') }}</a></li>
                @if (acl('view-profile-history'))
                    <li><a class="dropdown-item" href="{{ url('profile-history') }}">{{ __('Profile history') }}</a></li>
                @endif
                <li><a class="dropdown-item" href="{{ url('change-password') }}">Change Password</a></li>
                @if(hasRole('bnp') || hasRole('lsp') || hasRole('snp'))                   
                <li>
                    <a class="dropdown-item no-hover" id="downloadCertBtn" href="{{ route('certificate.download') }}" style="color: brown;">
                       
                        {{ __('Download Certificate') }}
                    </a>
                </li>
                @endif
                <li>
                    <button type="button" class="dropdown-item" data-bs-toggle="modal" data-bs-target="#logoutModal">
                        {{ __('Logout') }}
                    </button>
                </li>
            </ul>
        </li>
    </ul>
</nav>

<!-- ========== LOADER (added after nav) ========== -->
<div id="certLoader" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); z-index:9999; align-items:center; justify-content:center;">
    <div class="spinner-border text-light" role="status" style="width:3rem; height:3rem;">
        <span class="visually-hidden">Loading...</span>
    </div>
</div>

<!-- ========== TOAST CONTAINER ========== -->
<div style="position: fixed; top: 20px; right: 20px; z-index: 10000;" id="toastContainer"></div>

<div class="modal fade" id="deleteModalNew" tabindex="-1" aria-labelledby="confirmdeleteModalNewLabel"
    aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="confirmdeleteModalNewLabel">Confirm</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                Are you sure you want to read all the notification?
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="confirmDelete">Read</button>
            </div>
        </div>
    </div>
</div>

<script>
let selectedId = null;

$(document).ready(function() {
    function fetchNotifications() {
        $.get("{{ route('notifications.get') }}", function(data) {
            $('.notification-data li:not(:first)').remove();
            if (data.length > 0) {
                data.forEach(function(n) {
                    let link = n.link ? "{{ url('/') }}" + n.link : "#";
                  let item = `
                        <li>
                            <div class="notification-card">                                
                                <div class="delete-icon" data-id="${n.id}">
                                    <i class="fa fa-trash"></i>
                                </div>

                                <a href="${link}" class="notification-item" data-id="${n.id}">
                                    
                                    <div class="notification-title">
                                        ${n.template ? n.template.trigger_point : ''}
                                    </div>

                                    <div class="notification-text-wrapper">
                                        <span class="noti-icon">
                                            <i class="fa fa-info-circle text-success"></i>
                                        </span>

                                        <span class="notification-text">
                                            ${n.message}
                                        </span>
                                    </div>

                                    <div class="notification-time">
                                        ${formatDateTime(n.created_at)}
                                    </div>

                                </a>
                            </div>
                        </li>
                        `;

                    $('.notification-data').append(item);
                });

                $('#notificationCount').text(data.length);

            } else {
                $('.notification-data').append('<li class="p-2">No notifications</li>');
                $('#notificationCount').text('');
            }
        });
    }

    fetchNotifications();
    setInterval(fetchNotifications, 180000);

    // ✅ CLICK → MARK READ + REDIRECT
    $(document).on('click', '.notification-item', function(e) {
        e.preventDefault();
        let id = $(this).data('id');
        let link = $(this).attr('href');
        $.post("{{ url('notifications/mark-read') }}/" + id, {_token: '{{ csrf_token() }}'
        }, function() {
            if (link && link !== "#") {
                window.location.href = link;
            } else {
                fetchNotifications();
            }
        });
    });

    // ✅ DELETE ICON CLICK → OPEN MODAL
    $(document).on('click', '.delete-icon', function() {
        selectedId = $(this).data('id');
        $('#deleteModalNew').modal('show');
    });

    // ✅ CONFIRM DELETE
    $('#confirmDelete').click(function() {
        $.post("{{ url('notifications/mark-read') }}/" + selectedId, {
            _token: '{{ csrf_token() }}'
        }, function() {
            $('#deleteModalNew').modal('hide');
            fetchNotifications();
        });
    });

    // ✅ MARK ALL
    $('#markAllRead').click(function() {
        $.post("{{ route('notifications.markRead') }}", {
            _token: '{{ csrf_token() }}'
        }, function() {
            fetchNotifications();
        });
    });

    // ========== DOWNLOAD CERTIFICATE HANDLER ==========
const downloadBtn = document.getElementById('downloadCertBtn');
if (downloadBtn) {
    downloadBtn.addEventListener('click', function(e) {
        e.preventDefault();

        const loader = document.getElementById('certLoader');
        loader.style.display = 'flex';
        

        fetch(this.href)
            .then(response => {
                if (!response.ok) {
                    throw new Error('Server error: ' + response.status);
                }
                // Get the filename from the custom header, fallback to default
                const filename = response.headers.get('X-Filename') || 'SNP_Certificate.pdf';
                return response.blob().then(blob => ({ blob, filename }));
            })
            .then(({ blob, filename }) => {
                loader.style.display = 'none';
                const url = window.URL.createObjectURL(blob);
                const a = document.createElement('a');
                a.href = url;
                a.download = filename;   // Use the filename from the server
                document.body.appendChild(a);
                a.click();
                a.remove();
                window.URL.revokeObjectURL(url);
                showToast('Certificate downloaded successfully!', 'success');
            })
            .catch(error => {
                loader.style.display = 'none';
                showToast('Failed to download certificate. Please try again.', 'danger');
                console.error('Download error:', error);
            });
    });
}

    // Toast helper (Bootstrap 5)
    function showToast(message, type = 'info') {
    const container = document.getElementById('toastContainer');
    const toast = document.createElement('div');

    toast.className = `toast text-bg-${type} border-0`;
    toast.setAttribute('role', 'alert');
    toast.setAttribute('aria-live', 'assertive');
    toast.setAttribute('aria-atomic', 'true');

    toast.innerHTML = `
        <div class="d-flex align-items-center">
            <div class="toast-body flex-grow-1">
                ${message}
            </div>
            <button type="button"
                    class="btn-close btn-close-white me-2"
                    data-bs-dismiss="toast"
                    aria-label="Close">
            </button>
        </div>

        <!-- Progress Bar -->
        <div style="height:4px; background:rgba(255,255,255,.25);">
            <div class="toast-progress"
                 style="
                    width:100%;
                    height:100%;
                    background:#fff;
                    transition:width 10s linear;
                 ">
            </div>
        </div>
    `;

    container.appendChild(toast);

    const bsToast = new bootstrap.Toast(toast, {
        autohide: true,
        delay: 10000
    });

    bsToast.show();

    // Animate progress bar
    requestAnimationFrame(() => {
        toast.querySelector('.toast-progress').style.width = '0%';
    });

    toast.addEventListener('hidden.bs.toast', function () {
        toast.remove();
    });
}
});

function formatDateTime(dateString) {
    let date = new Date(dateString);

    return date.toLocaleString('en-IN', {
        day: '2-digit',
        month: 'short',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
        hour12: true
    });
}
</script>