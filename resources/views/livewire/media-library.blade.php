<?php

use App\Models\Attachment;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Validate;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\Volt\Component;
use Livewire\WithFileUploads;

new class extends Component {
    use WithFileUploads;

    #[Validate(['uploadFiles.*' => 'file|max:2048'])]
    public $uploadFiles = [];
    public $chunkSize = 5_000_000; // 5MB

    public $files = [];

    // New properties for selection mode
    public $selectionMode = false;
    public $selectedFileId = null;
    public $allowedTypes = ['image']; // Default to images only for featured images
    public $maxSelections = 1; // For featured image, typically 1

    public function mount($selectionMode = false, $selectedFileId = null, $allowedTypes = ['image'], $maxSelections = 1)
    {
        $this->selectionMode = $selectionMode;
        $this->selectedFileId = $selectedFileId;
        $this->allowedTypes = $allowedTypes;
        $this->maxSelections = $maxSelections;
        $this->loadFiles();
    }

    public function loadFiles()
    {
        $attachments = Attachment::latest()->get();
        $files = [];
        foreach ($attachments as $attachment) {
            $mediaData = $attachment->mediaFile()?->toArray();
            if ($mediaData) {
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
        }
        $this->files = $files;
    }

    public function updatedUploadFiles($value, $key)
    {
        $keyParts = explode('.', $key);
        if (count($keyParts) < 2) {
            return;
        }

        list($index, $attribute) = $keyParts;

        if ($attribute == 'fileChunk') {
            $fileDetails = $this->uploadFiles[intval($index)];
            Log::info('file details', ['file details' => $fileDetails]);
            // Final File
            $fileName  = $fileDetails['fileName'];
            $finalPath = Storage::path('/livewire-tmp/' . $fileName);

            // Chunk File
            $chunkName = $fileDetails['fileChunk']->getFileName();
            $chunkPath = Storage::path('/livewire-tmp/' . $chunkName);
            $chunk      = fopen($chunkPath, 'rb');
            $buff       = fread($chunk, $this->chunkSize);
            fclose($chunk);

            // Merge Together
            $final = fopen($finalPath, 'ab');
            fwrite($final, $buff);
            fclose($final);
            unlink($chunkPath);

            // Progress
            $curSize = Storage::size('/livewire-tmp/' . $fileName);
            Log::info('file size information', ['current' => $curSize, 'file size' => $fileDetails['fileSize']]);
            // NOTE: Hack way. I always set the progress with 100 as minimum
            $this->uploadFiles[$index]['progress'] = min(100, $curSize / $fileDetails['fileSize'] * 100);
            if ($this->uploadFiles[$index]['progress'] == 100) {
                $this->uploadFiles[$index]['fileRef'] = TemporaryUploadedFile::createFromLivewire('/' . $fileDetails['fileName']);
                $uploadedPath = $this->uploadFiles[$index]['fileRef']->storePubliclyAs(path: 'files', name: $fileName, options: 'public');
                $attachment = Attachment::create([
                    'title' => $fileName
                ]);

                $attachment
                    ->addMedia(Storage::disk('public')->path($uploadedPath))
                    ->toMediaCollection('file', 'r2');

                TemporaryUploadedFile::createFromLivewire('/' . $fileDetails['fileName'])->delete();
            }
        }

        $this->loadFiles();
    }

    // New method for selecting files in selection mode
    public function selectFileForParent($fileId)
    {
        if (!$this->selectionMode) {
            return;
        }

        $this->selectedFileId = $fileId;

        // Dispatch event to parent component
        $selectedFile = collect($this->files)->firstWhere('id', $fileId);
        Log::info('selected file', ['selectedFile' => $selectedFile]);
        $this->dispatch('fileSelected', $selectedFile);
    }

    // Method to confirm selection and close modal
    public function confirmSelection()
    {
        if ($this->selectedFileId) {
            $selectedFile = collect($this->files)->firstWhere('id', $this->selectedFileId);
            Log::info('dispatch file confirmed', ['selected file' => $selectedFile]);
            $this->dispatch('fileConfirmed', $selectedFile);
        }
    }

    public function messages()
    {
        return [
            'uploadFiles.*.image' => 'Each file must be an image.',
            'uploadFiles.*.max' => 'Each file must be smaller than 1MB.',
        ];
    }
}; ?>

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

/* Selection Mode Styles */
.media-item.selection-mode {
    cursor: pointer;
    transition: all 0.2s ease;
}

.media-item.selection-mode:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
}

.media-item.chosen {
    border: 3px solid #007bff;
    box-shadow: 0 0 0 2px rgba(0, 123, 255, 0.25);
}

.selection-indicator {
    position: absolute;
    top: 10px;
    right: 10px;
    background: #007bff;
    color: white;
    border-radius: 50%;
    width: 25px;
    height: 25px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 14px;
    font-weight: bold;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
}

.selection-footer {
    border-top: 1px solid #eee;
    padding: 20px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    background: #f8f9fa;
}

.selection-info {
    color: #666;
    font-size: 14px;
}

.selection-actions {
    display: flex;
    gap: 10px;
}

.selection-actions .btn {
    padding: 8px 16px;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    font-size: 14px;
}

.selection-actions .btn:disabled {
    opacity: 0.5;
    cursor: not-allowed;
}

.selection-actions .btn-primary {
    background: #007bff;
    color: white;
}

.selection-actions .btn-secondary {
    background: #6c757d;
    color: white;
}

/* Hide sidebar in selection mode */
.media-library[x-data] .media-sidebar {
    display: none;
}

/* Adjust grid width when sidebar is hidden */
.media-library .media-grid-container {
    width: 100%;
}

/* Media preview positioning for selection indicator */
.media-preview {
    position: relative;
}

/* Filter files that aren't allowed in selection mode */
.media-item.not-allowed {
    opacity: 0.5;
    cursor: not-allowed;
}

.media-item.not-allowed:hover {
    transform: none;
    box-shadow: none;
}
</style>

<div class="media-library" x-data="mediaLibrary()">
    <div class="media-header">
        <!-- Upload Area -->
        <div class="upload-area" @click="$refs.fileInput.click()" @dragover.prevent="dragOver = true"
            @dragleave.prevent="dragOver = false" @drop.prevent="handleDrop($event)" :class="{ 'dragover': dragOver }">
            <div class="upload-icon">📁</div>
            <div class="upload-text">
                Click to upload files or drag and drop
            </div>
            <input type="file" x-ref="fileInput" @change="handleFileSelect($event)" multiple
                accept="image/*,video/*,audio/*,.pdf,.doc,.docx,.txt" class="hidden">
        </div>

        <!-- Controls -->
        <div class="media-controls">
            <input type="text" class="search-input" placeholder="Search media files..." x-model="searchTerm"
                @input="filterFiles()">

            <select class="filter-select" x-model="filterType" @change="filterFiles()">
                <option value="all">All files</option>
                <option value="image">Images</option>
                <option value="video">Videos</option>
                <option value="audio">Audio</option>
                <option value="document">Documents</option>
            </select>

            <div class="view-toggle">
                <button class="view-btn" :class="{ 'active': viewMode === 'grid' }"
                    @click="viewMode = 'grid'">Grid</button>
                <button class="view-btn" :class="{ 'active': viewMode === 'list' }"
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
                        :class="[
                            viewMode + '-view',
                            { 'selected': selectedFile && selectedFile.id === file.id },
                            { 'selection-mode': selectionMode },
                            { 'chosen': selectionMode && selectedFile == file.id }
                        ]"
                        @click="selectionMode ? selectForParent(file) : selectFile(file)">

                        <div class="media-preview" :class="{ 'file-icon': !isImage(file.type) }">
                            <img x-show="isImage(file.type)" :src="file.url" :alt="file.name" loading="lazy">
                            <span x-show="!isImage(file.type)" x-text="getFileIcon(file.type)"></span>

                            <!-- Selection indicator for selection mode -->
                            <div x-show="selectionMode && selectedFileId == file.id" class="selection-indicator">
                                ✓
                            </div>
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
        <div class="media-sidebar" x-show="selectedFile && !selectionMode">
            <template x-if="selectedFile">
                <div>
                    <h3 class="sidebar-title">File Details</h3>

                    <div class="sidebar-preview">
                        <img x-show="isImage(selectedFile.type)" :src="selectedFile.url" :alt="selectedFile.name">
                        <span x-show="!isImage(selectedFile.type)" x-text="getFileIcon(selectedFile.type)"
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

    <div x-show="selectionMode" class="selection-footer">
        <div class="selection-info">
            <span x-show="selectedFileId">Selected file ready</span>
            <span x-show="!selectedFileId">Select a file to continue</span>
        </div>
        <div class="selection-actions">
            <button class="btn btn-secondary" @click="$dispatch('media-library-cancelled')">
                Cancel
            </button>
            <button class="btn btn-primary" :disabled="!selectedFileId" @click="confirmSelection()">
                Use Selected File
            </button>
        </div>
    </div>
</div>

@script
<script>
    window.mediaLibrary = function () {
        return {
            uploading: false,
            progress: 0,
            files: $wire.entangle('files').live,
            chunkStarts: [],
            filteredFiles: [],
            selectedFile: null,
            viewMode: 'grid',
            searchTerm: '',
            filterType: 'all',
            dragOver: false,
            selectionMode: $wire.entangle('selectionMode'),
            selectedFileId: $wire.entangle('selectedFileId'),
            allowedTypes: $wire.entangle('allowedTypes'),

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
                files.forEach((file, index) => {
                    $wire.set('uploadFiles.' + index + '.fileName', file.name);
                    $wire.set('uploadFiles.' + index + '.fileSize', file.size);
                    $wire.set('uploadFiles.' + index + '.progress', 0);
                    this.chunkStarts[index] = 0;
                    this.livewireUploadChunk(index, file);
                })
            },

            livewireUploadChunk(index, file) {
                const chunkEnd = Math.min(this.chunkStarts[index] + $wire.chunkSize, file.size);
                const chunk = file.slice(this.chunkStarts[index], chunkEnd);
                $wire.upload('uploadFiles.' + index + '.fileChunk', chunk,
                    () => {
                        this.files = $wire.files;
                        this.filterFiles();
                    },
                    () => {},
                    (e) => {
                    if (e.detail.progress == 100) {
                        this.chunkStarts[index] = Math.min(this.chunkStarts[index] + $wire.chunkSize, file.size);

                        if (this.chunkStarts[index] < file.size) {
                            let _time = Math.floor((Math.random() * 2000) + 1);
                            console.log('sleeping ', _time, 'before next chunk upload');
                            setTimeout(livewireUploadChunk, _time, index, file);
                        }
                    }
                })
            },

            selectFile(file) {
                if (this.selectionMode) return;
                this.selectedFile = this.selectedFile?.id === file.id ? null : file;
            },

            selectForParent(file) {
                if (!this.selectionMode) return;

                // Check if file type is allowed
                if (!this.isFileTypeAllowed(file.type)) {
                    alert('This file type is not allowed for selection');
                    return;
                }

                this.selectFileId = file.id;
                $wire.selectFileForParent(file.id);
            },

            confirmSelection() {
                $wire.confirmSelection();
            },

            deleteFile(fileId) {
                if (confirm('Are you sure you want to delete this file permanently?')) {
                    this.files = this.files.filter(file => file.id !== fileId);
                    if (this.selectedFile?.id === fileId) {
                        this.selectedFile = null;
                    }
                    if (this.selectedFileId === fileId) {
                        this.selectedFileId = null;
                    }
                    this.filterFiles();
                }
            },

            filterFiles() {
                let filtered = this.files;

                // In selection mode, filter by allowed types
                if (this.selectionMode && this.allowedTypes.length > 0) {
                    filtered = filtered.filter(file => this.isFileTypeAllowed(file.type));
                }

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

            isFileTypeAllowed(type) {
                if (!this.selectionMode || this.allowedTypes.length === 0) return true;

                return this.allowedTypes.some(allowedType => {
                    switch (allowedType) {
                        case 'image':
                            return type.startsWith('image/');
                        case 'video':
                            return type.startsWith('video/');
                        case 'audio':
                            return type.startsWith('audio/');
                        case 'document':
                            return type.includes('pdf') || type.includes('doc') || type.includes('text');
                        default:
                            return false;
                    }
                });
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
