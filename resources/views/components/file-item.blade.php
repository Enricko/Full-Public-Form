<div class="file-item">
    <div class="file-icon">
        @if (str_contains($file->file_type, 'pdf'))
            <i class="fas fa-file-pdf text-danger"></i>
        @elseif(str_contains($file->file_type, 'word') || str_contains($file->file_type, 'document'))
            <i class="fas fa-file-word text-primary"></i>
        @elseif(str_contains($file->file_type, 'excel') || str_contains($file->file_type, 'spreadsheet'))
            <i class="fas fa-file-excel text-success"></i>
        @elseif(str_contains($file->file_type, 'zip') || str_contains($file->file_type, 'archive'))
            <i class="fas fa-file-archive text-warning"></i>
        @elseif(str_contains($file->file_type, 'text'))
            <i class="fas fa-file-alt text-info"></i>
        @else
            <i class="fas fa-file text-muted"></i>
        @endif
    </div>
    <div class="file-info">
        <div class="file-name">{{ $file->file_name }}</div>
        <div class="file-meta">
            <span class="file-size">{{ number_format($file->file_size / 1024, 1) }} KB</span>
            <span class="file-type">{{ strtoupper(pathinfo($file->file_name, PATHINFO_EXTENSION)) }}</span>
        </div>
    </div>
    <a href="{{ $file->file_path }}" download class="btn btn-sm btn-outline-primary">
        <i class="fas fa-download"></i>
    </a>
</div>
