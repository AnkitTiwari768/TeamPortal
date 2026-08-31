<script>
async function loadAttributeDropdownOptions(code, targetElement, dependency = false, selectedVal = null, skipParent = false) {
    const element = document.querySelector(targetElement);
    if (element) {
        element.disabled = true;
        element.innerHTML = '<option value="">Loading...</option>';
    }
    try {
        const response = await fetch(`{{ url('get-dropdown-options') }}?code=${encodeURIComponent(code)}&dependency=${dependency ? 1 : 0}&skip_parent=${skipParent ? 1 : 0}`, {
            method: 'GET',
            headers: {
                'Accept': 'application/json'
            }
        });

        if (!response.ok) {
            throw new Error(`HTTP Error: ${response.status}`);
        }

        const res = await response.json();

        let options = '<option value="">Select</option>';

        if (res.status && Array.isArray(res.data)) {
            res.data.forEach(item => {
                const selected = selectedVal && String(item.id) === String(selectedVal) ? 'selected' : '';
                options += `<option value="${item.id}" ${selected}>${item.text}</option>`;
            });
        }

        if (element) {
            element.innerHTML = options;
        }

    } catch (error) {
        console.error('Error loading dropdown options:', error);
        if (element) {
            element.innerHTML = '<option value="">Select</option>';
        }
    } finally {
        if (element) {
            element.disabled = false;
        }
    }
}
</script>
