<?php

use App\Models\Attachment;
use App\Models\Media;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Validate;
use Livewire\Volt\Component;
use Livewire\WithFileUploads;

new class extends Component {

    use WithFileUploads;

    #[Validate(['uploadFiles.*' => 'file|max:2048'])]
    public $uploadFiles = [];

    public $files = [];

    public function mount()
    {
        $attachments = Attachment::latest()->get();
        $files = [];
        foreach ($attachments as $attachment) {
            $mediaData = $attachment->mediaFile()->toArray();
            $transformedData = [
                'id' => $mediaData['id'],
                'name' => $mediaData['name'],
                'type' => $mediaData['mime_type'],
                'size' => $mediaData['size'],
                'url' => $attachment->mediaFile()->getUrl(),
                'uploaded' => (new DateTime($mediaData['created_at']))->format('c'),
                'dimensions' => null,
            ];
            array_push($files, $transformedData);
        }
        $this->files = $files;
    }

    public function updatedUploadFiles()
    {
        try {
            $this->validate();

            foreach ($this->uploadFiles as $file) {
                $filename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
                $extension = pathinfo($file->getClientOriginalName(), PATHINFO_EXTENSION);
                $filename = $filename . '_' . time() . '.' . $extension;
                $uploadedPath = $file->storePubliclyAs(path: 'files', options: 'public', name: $filename);

                $attachment = Attachment::create([
                    'title' => $filename
                ]);

                $attachment
                    ->addMedia(Storage::disk('public')->path($uploadedPath))
                    ->toMediaCollection('file');
            }
        } catch (\Throwable $th) {
            Log::error('Upload failed: ' . $th->getMessage());
        } finally {

        }
    }

    public function messages()
    {
        return [
            'files.*.image' => 'Each file must be an image.',
            'files.*.max' => 'Each file must be smaller than 1MB.',
        ];
    }
}; ?>

<div>
    <div class="media-library" x-data="mediaLibrary()">
        <div class="media-header">
            <!-- Upload Area -->
            <div class="upload-area"
                @click="$refs.fileInput.click()"
                @dragover.prevent="dragOver = true"
                @dragleave.prevent="dragOver = false"
                @drop.prevent="handleDrop($event)"
                :class="{ 'dragover': dragOver }">
                <div class="upload-icon">📁</div>
                <div class="upload-text">
                    Click to upload files or drag and drop
                </div>
                <input type="file"
                    x-ref="fileInput"
                    @change="handleFileSelect($event)"
                    multiple
                    accept="image/*,video/*,audio/*,.pdf,.doc,.docx,.txt"
                    class="hidden">
            </div>

            <!-- Controls -->
            <div class="media-controls">
                <input type="text"
                        class="search-input"
                        placeholder="Search media files..."
                        x-model="searchTerm"
                        @input="filterFiles()">

                <select class="filter-select" x-model="filterType" @change="filterFiles()">
                    <option value="all">All files</option>
                    <option value="image">Images</option>
                    <option value="video">Videos</option>
                    <option value="audio">Audio</option>
                    <option value="document">Documents</option>
                </select>

                <div class="view-toggle">
                    <button class="view-btn"
                            :class="{ 'active': viewMode === 'grid' }"
                            @click="viewMode = 'grid'">Grid</button>
                    <button class="view-btn"
                            :class="{ 'active': viewMode === 'list' }"
                            @click="viewMode = 'list'">List</button>
                </div>
            </div>
        </div>

        <!-- Main Content -->
        <div class="media-content">
            <!-- Media Grid -->
            <div class="media-grid-container">
                <div class="media-grid" :class="viewMode + '-view'">
                    <template x-for="file in filteredFiles" :key="file.id">
                        <div class="media-item"
                             :class="[viewMode + '-view', { 'selected': selectedFile && selectedFile.id === file.id }]"
                             @click="selectFile(file)">

                            <div class="media-preview" :class="{ 'file-icon': !isImage(file.type) }">
                                <img x-show="isImage(file.type)" :src="file.url" :alt="file.name" loading="lazy">
                                <span x-show="!isImage(file.type)" x-text="getFileIcon(file.type)"></span>
                            </div>

                            <div class="media-info">
                                <div class="media-filename" x-text="file.name"></div>
                                <div class="media-size" x-text="formatFileSize(file.size)"></div>
                            </div>
                        </div>
                    </template>
                </div>

                <div x-cloak x-show="filteredFiles.length === 0" class="upload-area">
                    <div class="upload-icon">📂</div>
                    <div class="upload-text">
                        <span x-show="files.length === 0">No files uploaded yet</span>
                        <span x-show="files.length > 0">No files match your search</span>
                    </div>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="media-sidebar" x-show="selectedFile">
                <template x-if="selectedFile">
                    <div>
                        <h3 class="sidebar-title">File Details</h3>

                        <div class="sidebar-preview">
                            <img x-show="isImage(selectedFile.type)" :src="selectedFile.url" :alt="selectedFile.name">
                            <span x-show="!isImage(selectedFile.type)"
                                  x-text="getFileIcon(selectedFile.type)"
                                  style="font-size: 64px; color: #666;"></span>
                        </div>

                        <div class="sidebar-info">
                            <div class="info-row">
                                <span class="info-label">Filename:</span>
                                <span class="info-value" x-text="selectedFile.name"></span>
                            </div>
                            <div class="info-row">
                                <span class="info-label">File type:</span>
                                <span class="info-value" x-text="selectedFile.type"></span>
                            </div>
                            <div class="info-row">
                                <span class="info-label">File size:</span>
                                <span class="info-value" x-text="formatFileSize(selectedFile.size)"></span>
                            </div>
                            <div class="info-row">
                                <span class="info-label">Uploaded:</span>
                                <span class="info-value" x-text="formatDate(selectedFile.uploaded)"></span>
                            </div>
                            <div class="info-row" x-show="isImage(selectedFile.type) && selectedFile.dimensions">
                                <span class="info-label">Dimensions:</span>
                                <span class="info-value" x-text="selectedFile.dimensions"></span>
                            </div>
                        </div>

                        <button class="delete-btn" @click="deleteFile(selectedFile.id)">
                            Delete Permanently
                        </button>
                    </div>
                </template>
            </div>
        </div>
    </div>
</div>

@assets
<style>
.media-library {
    max-width: 1200px;
    margin: 0 auto 20px auto;
    background: white;
    border-radius: 8px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    overflow: hidden;
}

.media-header {
    padding: 20px;
    border-bottom: 1px solid #ddd;
    background: #fff;
}

.media-controls {
    display: flex;
    gap: 15px;
    align-items: center;
    flex-wrap: wrap;
}

.upload-btn {
    background: #0073aa;
    color: white;
    border: none;
    padding: 8px 16px;
    border-radius: 4px;
    cursor: pointer;
    font-size: 14px;
    transition: background 0.2s;
}

.upload-btn:hover {
    background: #005a87;
}

.search-input {
    padding: 8px 12px;
    border: 1px solid #ddd;
    border-radius: 4px;
    font-size: 14px;
    width: 250px;
}

.view-toggle {
    display: flex;
    border: 1px solid #ddd;
    border-radius: 4px;
    overflow: hidden;
}

.view-btn {
    padding: 8px 12px;
    background: white;
    border: none;
    cursor: pointer;
    font-size: 14px;
    transition: background 0.2s;
}

.view-btn.active {
    background: #0073aa;
    color: white;
}

.filter-select {
    padding: 8px 12px;
    border: 1px solid #ddd;
    border-radius: 4px;
    font-size: 14px;
    background: white;
}

.media-content {
    display: flex;
    min-height: 500px;
}

.media-grid-container {
    flex: 1;
    padding: 20px;
    overflow-y: auto;
    max-height: 600px;
}

.media-grid {
    display: grid;
    gap: 15px;
}

.media-grid.grid-view {
    grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
}

.media-grid.list-view {
    grid-template-columns: 1fr;
}

.media-item {
    border: 2px solid transparent;
    border-radius: 8px;
    overflow: hidden;
    cursor: pointer;
    transition: all 0.2s;
    background: #f9f9f9;
    position: relative;
}

.media-item:hover {
    border-color: #0073aa;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

.media-item.selected {
    border-color: #0073aa;
    background: #e7f3ff;
}

.media-item.grid-view {
    aspect-ratio: 1;
}

.media-item.list-view {
    display: flex;
    align-items: center;
    padding: 10px;
    min-height: 60px;
}

.media-preview {
    width: 100%;
    height: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
    background: #f0f0f1;
    position: relative;
    overflow: hidden;
}

.media-preview img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.media-preview.file-icon {
    font-size: 48px;
    color: #666;
}

.list-view .media-preview {
    width: 50px;
    height: 50px;
    flex-shrink: 0;
    border-radius: 4px;
    margin-right: 15px;
}

.media-info {
    padding: 10px;
    font-size: 12px;
    color: #666;
    text-align: center;
}

.list-view .media-info {
    flex: 1;
    text-align: left;
    padding: 0;
}

.media-filename {
    font-weight: 500;
    color: #1d2327;
    margin-bottom: 2px;
    word-break: break-word;
}

.media-size {
    color: #999;
}

.media-sidebar {
    width: 300px;
    background: #f9f9f9;
    border-left: 1px solid #ddd;
    padding: 20px;
    overflow-y: auto;
}

.sidebar-title {
    font-size: 18px;
    font-weight: 600;
    margin-bottom: 15px;
    color: #1d2327;
}

.sidebar-preview {
    width: 100%;
    height: 200px;
    background: #f0f0f1;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-bottom: 15px;
    overflow: hidden;
}

.sidebar-preview img {
    width: 100%;
    height: 100%;
    object-fit: contain;
}

.sidebar-info {
    display: flex;
    flex-direction: column;
    gap: 10px;
}

.info-row {
    display: flex;
    justify-content: space-between;
    padding: 8px 0;
    border-bottom: 1px solid #eee;
    font-size: 14px;
}

.info-label {
    font-weight: 500;
    color: #666;
}

.info-value {
    color: #1d2327;
    text-align: right;
    max-width: 150px;
    word-break: break-word;
}

.delete-btn {
    background: #d63638;
    color: white;
    border: none;
    padding: 8px 16px;
    border-radius: 4px;
    cursor: pointer;
    font-size: 14px;
    margin-top: 15px;
    width: 100%;
    transition: background 0.2s;
}

.delete-btn:hover {
    background: #b32d2e;
}

.upload-area {
    border: 2px dashed #ccc;
    border-radius: 8px;
    padding: 40px;
    text-align: center;
    margin-bottom: 20px;
    transition: border-color 0.2s;
    cursor: pointer;
}

.upload-area:hover,
.upload-area.dragover {
    border-color: #0073aa;
    background: #f0f8ff;
}

.upload-icon {
    font-size: 48px;
    color: #ccc;
    margin-bottom: 10px;
}

.upload-text {
    color: #666;
    font-size: 16px;
}

@media (max-width: 768px) {
    .media-content {
        flex-direction: column;
    }

    .media-sidebar {
        width: 100%;
        border-left: none;
        border-top: 1px solid #ddd;
    }

    .media-controls {
        flex-direction: column;
        align-items: stretch;
    }

    .search-input {
        width: 100%;
    }
}
</style>
<link rel="stylesheet" href="css/notyf.min.css">
<script src="js/notyf.min.js"></script>
@endassets

@script
<script>
    window.mediaLibrary = function () {
        return {
            uploading: false,
            progress: 0,
            files: $wire.entangle('files'),
            filteredFiles: [],
            selectedFile: null,
            viewMode: 'grid',
            searchTerm: '',
            filterType: 'all',
            dragOver: false,
            nextId: 4,

            init() {
                this.filteredFiles = this.files;
            },

            handleFileSelect(event) {
                const files = Array.from(event.target.files);
                this.uploadFiles(files);
            },

            handleDrop(event) {
                this.dragOver = false;
                const files = Array.from(event.dataTransfer.files);
                this.uploadFiles(files);
            },

            uploadFiles(files) {
                $wire.uploadMultiple('uploadFiles', files,
                    (success) => {
                        (new Notyf()).success('File successfully uploaded.');
                        files.forEach(file => {
                            const fileObj = {
                                id: this.nextId++,
                                name: file.name,
                                type: file.type,
                                size: file.size,
                                url: this.isImage(file.type) ? URL.createObjectURL(file) : '',
                                uploaded: new Date(),
                                dimensions: null
                            };

                            // Get image dimensions if it's an image
                            if (this.isImage(file.type)) {
                                const img = new Image();
                                img.onload = () => {
                                    fileObj.dimensions = `${img.width}x${img.height}`;
                                };
                                img.src = fileObj.url;
                            }

                            this.files.unshift(fileObj);
                        });

                        this.filterFiles();
                    },
                    (error) => {
                        (new Notyf()).error('Cannot upload file. File size is too large.');
                    }
                );
            },

            selectFile(file) {
                this.selectedFile = this.selectedFile?.id === file.id ? null : file;
            },

            deleteFile(fileId) {
                if (confirm('Are you sure you want to delete this file permanently?')) {
                    this.files = this.files.filter(file => file.id !== fileId);
                    if (this.selectedFile?.id === fileId) {
                        this.selectedFile = null;
                    }
                    this.filterFiles();
                }
            },

            filterFiles() {
                let filtered = this.files;

                // Filter by type
                if (this.filterType !== 'all') {
                    filtered = filtered.filter(file => {
                        switch (this.filterType) {
                            case 'image':
                                return file.type.startsWith('image/');
                            case 'video':
                                return file.type.startsWith('video/');
                            case 'audio':
                                return file.type.startsWith('audio/');
                            case 'document':
                                return file.type.includes('pdf') ||
                                        file.type.includes('doc') ||
                                        file.type.includes('text');
                            default:
                                return true;
                        }
                    });
                }

                // Filter by search term
                if (this.searchTerm) {
                    const term = this.searchTerm.toLowerCase();
                    filtered = filtered.filter(file =>
                        file.name.toLowerCase().includes(term) ||
                        file.type.toLowerCase().includes(term)
                    );
                }

                this.filteredFiles = filtered;
            },

            isImage(type) {
                return type && type.startsWith('image/');
            },

            getFileIcon(type) {
                if (type.startsWith('video/')) return '🎥';
                if (type.startsWith('audio/')) return '🎵';
                if (type.includes('pdf')) return '📄';
                if (type.includes('doc')) return '📝';
                if (type.includes('text')) return '📃';
                return '📁';
            },

            formatFileSize(bytes) {
                if (bytes === 0) return '0 Bytes';
                const k = 1024;
                const sizes = ['Bytes', 'KB', 'MB', 'GB'];
                const i = Math.floor(Math.log(bytes) / Math.log(k));
                return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
            },

            formatDate(date) {
                // console.log(new Date(date));
                return (new Date(date)).toLocaleDateString('en-US', {
                    year: 'numeric',
                    month: 'short',
                    day: 'numeric',
                    hour: '2-digit',
                    minute: '2-digit'
                });
            }
        }
    }
</script>
@endscript
