<?php
$currentPage = 'profile';
$pageTitle = 'Profile';
$pageSubtitle = 'Manage your account details';
require_once __DIR__ . '/includes/layout.php';
?>
<div class="profile-form">
    <div class="card">
        <h2 style="margin-bottom:1rem">Profile Information</h2>
        <form id="profileForm">
            <div class="form-group">
                <label for="profileName">Name *</label>
                <input type="text" id="profileName" name="name" required>
            </div>
            <div class="form-group">
                <label for="profileEmail">Email *</label>
                <input type="email" id="profileEmail" name="email" required>
            </div>
            <button type="submit" class="btn btn-primary">Save Changes</button>
        </form>
    </div>

    <div class="card">
        <h2 style="margin-bottom:1rem">Change Password</h2>
        <form id="passwordForm">
            <div class="form-group">
                <label for="currentPassword">Current Password *</label>
                <input type="password" id="currentPassword" name="current_password" required>
            </div>
            <div class="form-group">
                <label for="newPassword">New Password *</label>
                <input type="password" id="newPassword" name="new_password" required minlength="6" placeholder="At least 6 characters">
            </div>
            <div class="form-group">
                <label for="confirmPassword">Confirm New Password *</label>
                <input type="password" id="confirmPassword" name="confirm_password" required minlength="6">
            </div>
            <button type="submit" class="btn btn-primary">Update Password</button>
        </form>
    </div>
</div>

<script>
const base = '<?= APP_URL ?>/api';

async function loadProfile() {
    const user = await fetch(base + '/profile.php').then(r => r.json());
    document.getElementById('profileName').value = user.name || '';
    document.getElementById('profileEmail').value = user.email || '';
}

document.getElementById('profileForm').addEventListener('submit', async (e) => {
    e.preventDefault();
    const data = {
        name: document.getElementById('profileName').value.trim(),
        email: document.getElementById('profileEmail').value.trim()
    };
    try {
        const res = await fetch(base + '/profile.php', {
            method: 'PUT',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(data)
        });
        const result = await res.json();
        if (!res.ok) throw new Error(result.error || 'Failed to update');
        if (typeof showToast === 'function') showToast('Profile updated successfully', 'success');
        else alert('Profile updated successfully');
        window.location.reload();
    } catch (err) {
        if (typeof showToast === 'function') showToast(err.message || 'Failed to update profile', 'error');
        else alert(err.message || 'Failed to update profile');
    }
});

document.getElementById('passwordForm').addEventListener('submit', async (e) => {
    e.preventDefault();
    const newPassword = document.getElementById('newPassword').value;
    const confirmPassword = document.getElementById('confirmPassword').value;

    if (newPassword !== confirmPassword) {
        if (typeof showToast === 'function') showToast('New passwords do not match', 'error');
        else alert('New passwords do not match');
        return;
    }

    const data = {
        current_password: document.getElementById('currentPassword').value,
        new_password: newPassword
    };

    try {
        const res = await fetch(base + '/profile.php', {
            method: 'PUT',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(data)
        });
        const result = await res.json();
        if (!res.ok) throw new Error(result.error || 'Failed to update password');
        if (typeof showToast === 'function') showToast('Password updated successfully', 'success');
        else alert('Password updated successfully');
        document.getElementById('passwordForm').reset();
    } catch (err) {
        if (typeof showToast === 'function') showToast(err.message || 'Failed to update password', 'error');
        else alert(err.message || 'Failed to update password');
    }
});

document.addEventListener('DOMContentLoaded', loadProfile);
</script>
<?php require_once __DIR__ . '/includes/layout-end.php'; ?>
