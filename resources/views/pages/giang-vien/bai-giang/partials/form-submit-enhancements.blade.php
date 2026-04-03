@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const lectureForm = document.querySelector('form[action="{{ $formAction }}"]');

    if (!lectureForm) {
        return;
    }

    let actionInput = lectureForm.querySelector('input[type="hidden"][name="hanh_dong"][data-managed-action="1"]');

    if (!actionInput) {
        actionInput = document.createElement('input');
        actionInput.type = 'hidden';
        actionInput.name = 'hanh_dong';
        actionInput.dataset.managedAction = '1';
        actionInput.value = @json(old('hanh_dong', 'luu_nhap'));
        lectureForm.prepend(actionInput);
    }

    lectureForm.querySelectorAll('button[type="submit"][name="hanh_dong"]').forEach(function (button) {
        const actionValue = button.value || 'luu_nhap';

        button.dataset.action = actionValue;
        button.removeAttribute('name');
        button.removeAttribute('value');
        button.addEventListener('click', function () {
            actionInput.value = actionValue;
        });
    });

    const phanCongSelect = document.getElementById('phan_cong_id');

    if (phanCongSelect && !phanCongSelect.value && phanCongSelect.options.length === 2) {
        phanCongSelect.selectedIndex = 1;
        phanCongSelect.dispatchEvent(new Event('change', { bubbles: true }));
    }
});
</script>
@endpush
