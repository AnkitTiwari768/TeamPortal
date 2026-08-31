@if(isset($userRolesList) && count($userRolesList) > 1)
<div class="mb-3 mt-2 role-tabs-container">
    <div class="d-flex align-items-center flex-wrap gap-2">

        <span class="fw-bold me-2 text-muted small">Switch Role:</span>

        <ul class="nav nav-pills custom-role-tabs mb-0" id="roleTabs">

            {{-- Individual role tabs — active is driven purely by the session active_role --}}
            @foreach($userRolesList as $role)
                <li class="nav-item">
                    <a class="nav-link {{ $activeRole === $role->slug ? 'active shadow-sm' : '' }}"
                       href="javascript:void(0)"
                       data-role="{{ $role->slug }}"
                       onclick="switchRole('{{ $role->slug }}', this)">
                        <i class="bi bi-person-badge me-1"></i> {{ $role->name }}
                    </a>
                </li>
            @endforeach

        </ul>

    </div>
</div>

<script>
/**
 * Switch to a single role.
 * Immediately marks the tab as active for visual feedback, then reloads after API confirms.
 *
 * @param {string}      roleSlug  - The role slug (e.g. 'snp', 'lsp', 'bnp')
 * @param {HTMLElement} tabEl     - The clicked <a> element
 */
function switchRole(roleSlug, tabEl) {
    // Skip if this tab is already active
    if (tabEl && tabEl.classList.contains('active')) {
        return;
    }

    // Immediate UI feedback: highlight clicked tab, remove active from others
    document.querySelectorAll('#roleTabs .nav-link').forEach(function(t) {
        t.classList.remove('active', 'shadow-sm');
    });
    if (tabEl) {
        tabEl.classList.add('active', 'shadow-sm');
        tabEl.innerHTML = '<span class="spinner-border spinner-border-sm me-1" role="status"></span>' + tabEl.innerHTML;
    }

    fetch('{{ url('switch-role') }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({ role: roleSlug })
    })
    .then(function(response) { return response.json(); })
    .then(function(data) {
        if (data.success) {
            window.location.reload();
        } else {
            // Revert visual change on failure
            if (tabEl) tabEl.classList.remove('active', 'shadow-sm');
            alert(data.message || 'Failed to switch role. Please try again.');
        }
    })
    .catch(function(error) {
        console.error('switchRole error:', error);
        if (tabEl) tabEl.classList.remove('active', 'shadow-sm');
    });
}
</script>

<style>
.role-tabs-container {
    background: #fff;
    padding: 10px 20px;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.05);
    margin-bottom: 16px;
    border-left: 4px solid #0d6efd;
}

/* Individual role tabs */
.custom-role-tabs .nav-link {
    border-radius: 20px;
    font-weight: 500;
    padding: 5px 14px;
    margin-right: 4px;
    color: #495057;
    background: #f8f9fa;
    border: 1px solid #dee2e6;
    transition: background 0.18s ease, border-color 0.18s ease, color 0.18s ease;
    font-size: 0.84rem;
    user-select: none;
}
.custom-role-tabs .nav-link:hover {
    background: #e9ecef;
    border-color: #adb5bd;
    color: #212529;
}
.custom-role-tabs .nav-link.active {
    background: #0d6efd;
    color: #fff;
    border-color: #0d6efd;
    pointer-events: none;   /* prevent double-clicking the already active tab */
}
</style>
@endif
