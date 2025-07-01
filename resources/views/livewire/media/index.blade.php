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
                    ->toMediaCollection('files');
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
    <div class="media-library" x-data="mediaLibrary()"
        x-on:livewire-upload-start="uploading = true"
        x-on:livewire-upload-finish="uploading = false"
        x-on:livewire-upload-cancel="uploading = false"
        x-on:livewire-upload-error="uploading = false"
        x-on:livewire-upload-progress="progress = $event.detail.progress"
    >

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

        @error('files.*')
            <span class="error">{{ $message }}</span>
        @enderror

        <div wire:loading wire:target="files">Uploading...</div>

        <div x-show="uploading">
            <progress max="100" x-bind:value="progress"></progress>
        </div>
    </div>
</div>

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
                        console.log(success);
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
                    () => {
                        // Error callback
                        console.log('error triggered');
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
