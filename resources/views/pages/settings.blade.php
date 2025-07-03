@extends('index')

@section('title', 'Settings - PublicForum')

@section('content')

<div class="card settings-card">
  <div class="card-header">
    <h4>Settings</h4>
  </div>
  
  <div class="card-body">
    <!-- Success Message -->
    <div id="settingsSuccessMessage" class="alert alert-success" style="display: none;"></div>
    
    <!-- Error Message -->
    <div id="settingsErrorMessage" class="alert alert-danger" style="display: none;"></div>
    
    <form id="settingsForm">
      @csrf
      
      <div class="setting-section">
        <div class="setting-row">
          <div class="setting-row-text">
            <div class="setting-label">Email Notifications</div>
            <div class="setting-description">Receive email updates about activity on PublicForum</div>
          </div>
          <label class="toggle-switch">
            <input type="checkbox" id="emailToggle" {{ ($currentUser->email_notifications ?? true) ? 'checked' : '' }}>
            <span class="toggle-slider"></span>
          </label>
        </div>
      </div>
      
      
      <div class="setting-section">
        <div class="setting-label">Email Address</div>
        <button type="button" class="btn btn-setting" data-bs-toggle="modal" data-bs-target="#emailModal">
          <span id="currentEmailDisplay">{{ $currentUser->email }}</span>
          <i class="fas fa-chevron-right"></i>
        </button>
      </div>
      
      
      <div class="setting-section">
        <div class="setting-label">Password</div>
        <button type="button" class="btn btn-setting" data-bs-toggle="modal" data-bs-target="#passwordModal">
          <span>Change password</span>
          <i class="fas fa-chevron-right"></i>
        </button>
      </div>

      {{-- <!-- Additional Profile Settings -->
      <div class="setting-section">
        <div class="setting-label">Username</div>
        <div class="mb-3">
          <input type="text" class="form-control" id="username" name="username" value="{{ $currentUser->username }}" placeholder="Enter username">
        </div>
      </div>

      <div class="setting-section">
        <div class="setting-label">Display Name</div>
        <div class="mb-3">
          <input type="text" class="form-control" id="display_name" name="display_name" value="{{ $currentUser->display_name }}" placeholder="Enter display name">
        </div>
      </div>

      <div class="setting-section">
        <div class="setting-label">Bio</div>
        <div class="mb-3">
          <textarea class="form-control" id="bio" name="bio" rows="3" placeholder="Tell us about yourself">{{ $currentUser->bio }}</textarea>
        </div>
      </div>

      <div class="setting-section">
        <button type="submit" class="btn btn-primary">Save Profile Settings</button>
      </div> --}}
    </form>
    
  </div>
</div>


<!-- Email Change Modal -->
<div class="modal fade" id="emailModal" tabindex="-1" aria-labelledby="emailModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="emailModalLabel">Change Email</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div id="emailErrorMessage" class="alert alert-danger" style="display: none;"></div>
        <form id="emailChangeForm">
          @csrf
          <div class="mb-3">
            <label for="currentEmail" class="setting-label">Current Email</label>
            <input type="email" class="form-control" id="currentEmail" value="{{ $currentUser->email }}" disabled />
          </div>
          <div class="mb-3">
            <label for="newEmail" class="setting-label">New Email</label>
            <input type="email" class="form-control" id="newEmail" name="new_email" placeholder="Enter new email" required />
          </div>
          <div class="mb-3">
            <label for="confirmPassword" class="setting-label">Confirm Password</label>
            <input type="password" class="form-control" id="confirmPassword" name="current_password" placeholder="Enter your password" required />
          </div>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        <button type="button" class="btn btn-danger" id="saveEmailBtn">
          <span class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
          Save Changes
        </button>
      </div>
    </div>
  </div>
</div>


<!-- Password Change Modal -->
<div class="modal fade" id="passwordModal" tabindex="-1" aria-labelledby="passwordModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="passwordModalLabel">Change Password</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div id="passwordErrorMessage" class="alert alert-danger" style="display: none;"></div>
        <form id="passwordChangeForm">
          @csrf
          <div class="mb-3">
            <label for="currentPassword" class="setting-label">Current Password</label>
            <input type="password" class="form-control" id="currentPassword" name="current_password" placeholder="Enter current password" required />
          </div>
          <div class="mb-3">
            <label for="newPassword" class="setting-label">New Password</label>
            <input type="password" class="form-control" id="newPassword" name="new_password" placeholder="Enter new password" required />
          </div>
          <div class="mb-3">
            <label for="confirmNewPassword" class="setting-label">Confirm New Password</label>
            <input type="password" class="form-control" id="confirmNewPassword" name="new_password_confirmation" placeholder="Confirm new password" required />
          </div>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        <button type="button" class="btn btn-danger" id="savePasswordBtn">
          <span class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
          Save Changes
        </button>
      </div>
    </div>
  </div>
</div>

<script>
  // CSRF token for all AJAX requests
  function getCSRFToken() {
    // Try multiple methods to get CSRF token
    const metaToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
    const inputToken = document.querySelector('input[name="_token"]')?.value;
    const hiddenToken = document.querySelector('input[type="hidden"][name="_token"]')?.value;
    
    return metaToken || inputToken || hiddenToken;
  }
  
  const csrfToken = getCSRFToken();
  
  // Log for debugging
  console.log('CSRF Token found:', csrfToken ? 'Yes' : 'No');

  // Helper function to show messages
  function showMessage(elementId, message, isError = false) {
    const element = document.getElementById(elementId);
    
    // Hide other message types first
    if (isError) {
      document.getElementById('settingsSuccessMessage').style.display = 'none';
    } else {
      document.getElementById('settingsErrorMessage').style.display = 'none';
    }
    
    element.textContent = message;
    element.style.display = 'block';
    
    // Clear any existing timeout
    if (element.hideTimeout) {
      clearTimeout(element.hideTimeout);
    }
    
    // Set new timeout
    element.hideTimeout = setTimeout(() => {
      element.style.display = 'none';
    }, 5000);
  }

  // Helper function to show loading state
  function setLoadingState(buttonId, loading = true) {
    const button = document.getElementById(buttonId);
    const spinner = button.querySelector('.spinner-border');
    
    if (loading) {
      button.disabled = true;
      if (spinner) spinner.classList.remove('d-none');
    } else {
      button.disabled = false;
      if (spinner) spinner.classList.add('d-none');
    }
  }

  // Handle email notifications toggle
  document.getElementById('emailToggle').addEventListener('change', function() {
    const enabled = this.checked;
    const originalState = !enabled; // Store original state for revert
    
    fetch('/settings/email-notifications', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': csrfToken,
        'Accept': 'application/json'
      },
      body: JSON.stringify({
        email_notifications: enabled
      })
    })
    .then(response => {
      console.log('Response status:', response.status);
      if (!response.ok) {
        throw new Error(`HTTP error! status: ${response.status}`);
      }
      return response.json();
    })
    .then(data => {
      console.log('Response data:', data);
      if (data.success) {
        showMessage('settingsSuccessMessage', 'Email notification settings updated successfully');
      } else {
        showMessage('settingsErrorMessage', data.message || 'Failed to update email notifications', true);
        // Revert toggle on error
        this.checked = originalState;
      }
    })
    .catch(error => {
      console.error('Fetch error:', error);
      showMessage('settingsErrorMessage', 'An error occurred while updating settings', true);
      // Revert toggle on error
      this.checked = originalState;
    });
  });

  // Handle main settings form submission (profile settings)
  document.getElementById('settingsForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const data = Object.fromEntries(formData);
    
    fetch('/settings/profile', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': csrfToken,
        'Accept': 'application/json'
      },
      body: JSON.stringify(data)
    })
    .then(response => {
      if (!response.ok) {
        throw new Error(`HTTP error! status: ${response.status}`);
      }
      return response.json();
    })
    .then(data => {
      if (data.success) {
        showMessage('settingsSuccessMessage', 'Profile settings updated successfully');
      } else {
        const errorMessage = data.errors ? 
          Object.values(data.errors).flat().join(', ') : 
          (data.message || 'Failed to update profile settings');
        showMessage('settingsErrorMessage', errorMessage, true);
      }
    })
    .catch(error => {
      console.error('Error:', error);
      showMessage('settingsErrorMessage', 'An error occurred while updating profile settings', true);
    });
  });

  // Handle email change
  document.getElementById('saveEmailBtn').addEventListener('click', function() {
    const form = document.getElementById('emailChangeForm');
    const formData = new FormData(form);
    const data = Object.fromEntries(formData);
    
    console.log('Sending email change request with data:', data);
    
    // Clear previous error messages
    document.getElementById('emailErrorMessage').style.display = 'none';
    
    setLoadingState('saveEmailBtn', true);
    
    fetch('/settings/email', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': csrfToken,
        'Accept': 'application/json'
      },
      body: JSON.stringify(data)
    })
    .then(response => {
      console.log('Email change response status:', response.status);
      console.log('Email change response headers:', response.headers);
      
      // Let's see the actual response text first
      return response.text().then(text => {
        console.log('Raw response text:', text);
        
        // Try to parse as JSON
        try {
          const jsonData = JSON.parse(text);
          return { ok: response.ok, status: response.status, data: jsonData };
        } catch (e) {
          console.error('Failed to parse JSON:', e);
          throw new Error(`Server returned HTML instead of JSON. Status: ${response.status}`);
        }
      });
    })
    .then(result => {
      setLoadingState('saveEmailBtn', false);
      
      console.log('Parsed email change result:', result);
      
      if (result.ok && result.data.success) {
        // Close modal
        const emailModal = bootstrap.Modal.getInstance(document.getElementById('emailModal'));
        emailModal.hide();
        
        // Update displayed email
        document.getElementById('currentEmailDisplay').textContent = result.data.new_email;
        document.getElementById('currentEmail').value = result.data.new_email;
        
        // Show success message
        showMessage('settingsSuccessMessage', 'Email address updated successfully');
        
        // Clear form
        form.reset();
      } else {
        const errorMessage = result.data.errors ? 
          Object.values(result.data.errors).flat().join(', ') : 
          (result.data.message || 'Failed to update email');
        document.getElementById('emailErrorMessage').textContent = errorMessage;
        document.getElementById('emailErrorMessage').style.display = 'block';
      }
    })
    .catch(error => {
      setLoadingState('saveEmailBtn', false);
      console.error('Email change error:', error);
      document.getElementById('emailErrorMessage').textContent = 'An error occurred while updating email: ' + error.message;
      document.getElementById('emailErrorMessage').style.display = 'block';
    });
  });

  // Handle password change
  document.getElementById('savePasswordBtn').addEventListener('click', function() {
    const form = document.getElementById('passwordChangeForm');
    const formData = new FormData(form);
    const data = Object.fromEntries(formData);
    
    console.log('Sending password change request with data:', data);
    
    // Clear previous error messages
    document.getElementById('passwordErrorMessage').style.display = 'none';
    
    // Client-side validation
    if (data.new_password !== data.new_password_confirmation) {
      document.getElementById('passwordErrorMessage').textContent = 'New passwords do not match';
      document.getElementById('passwordErrorMessage').style.display = 'block';
      return;
    }
    
    setLoadingState('savePasswordBtn', true);
    
    fetch('/settings/password', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': csrfToken,
        'Accept': 'application/json'
      },
      body: JSON.stringify(data)
    })
    .then(response => {
      console.log('Password change response status:', response.status);
      console.log('Password change response headers:', response.headers);
      
      // Let's see the actual response text first
      return response.text().then(text => {
        console.log('Raw password response text:', text);
        
        // Try to parse as JSON
        try {
          const jsonData = JSON.parse(text);
          return { ok: response.ok, status: response.status, data: jsonData };
        } catch (e) {
          console.error('Failed to parse JSON:', e);
          throw new Error(`Server returned HTML instead of JSON. Status: ${response.status}`);
        }
      });
    })
    .then(result => {
      setLoadingState('savePasswordBtn', false);
      
      console.log('Parsed password change result:', result);
      
      if (result.ok && result.data.success) {
        // Close modal
        const passwordModal = bootstrap.Modal.getInstance(document.getElementById('passwordModal'));
        passwordModal.hide();
        
        // Show success message
        showMessage('settingsSuccessMessage', 'Password updated successfully');
        
        // Clear form
        form.reset();
      } else {
        const errorMessage = result.data.errors ? 
          Object.values(result.data.errors).flat().join(', ') : 
          (result.data.message || 'Failed to update password');
        document.getElementById('passwordErrorMessage').textContent = errorMessage;
        document.getElementById('passwordErrorMessage').style.display = 'block';
      }
    })
    .catch(error => {
      setLoadingState('savePasswordBtn', false);
      console.error('Password change error:', error);
      document.getElementById('passwordErrorMessage').textContent = 'An error occurred while updating password: ' + error.message;
      document.getElementById('passwordErrorMessage').style.display = 'block';
    });
  });
</script>
@endsection