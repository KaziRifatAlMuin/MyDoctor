<!-- Image Modal -->
<div class="modal fade" id="imageModal" tabindex="-1">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content bg-transparent border-0">
            <div class="modal-body p-0 text-center">
                <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close" style="background: none; border: none; font-size: 24px; line-height: 1; opacity: 0.5; cursor: pointer; position: absolute; right: 10px; top: 10px; z-index: 10;">
                    <span aria-hidden="true">×</span>
                </button>
                <img src="" id="modalImage" class="img-fluid" style="max-height: 90vh;">
            </div>
        </div>
    </div>
</div>

<!-- Video Modal -->
<div class="modal fade" id="videoModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content bg-transparent border-0">
            <div class="modal-header border-0">
                <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close" style="background: none; border: none; font-size: 24px; line-height: 1; opacity: 0.5; cursor: pointer;">
                    <span aria-hidden="true">×</span>
                </button>
            </div>
            <div class="modal-body p-0 text-center">
                <div id="videoModalContent" style="position: relative; width: 100%; background: #000; border-radius: 8px; overflow: hidden;"></div>
            </div>
        </div>
    </div>
</div>

<!-- Post View Modal -->
<div class="modal fade" id="postModal" tabindex="-1" aria-labelledby="postModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="postModalLabel">
                    <i class="fas fa-newspaper me-2" style="color: #1877f2;"></i>
                    Post
                </h5>
                <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close" style="background: none; border: none; font-size: 24px; line-height: 1; opacity: 0.5; cursor: pointer;">
                    <span aria-hidden="true">×</span>
                </button>
            </div>
            <div class="modal-body p-0" id="postModalBody">
                <div class="text-center py-5">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- User Profile Modal -->
<div class="modal fade" id="userModal" tabindex="-1" aria-labelledby="userModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="userModalLabel">
                    <i class="fas fa-user-circle me-2 text-primary"></i>
                    User Profile
                </h5>
                <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close" style="background: none; border: none; font-size: 24px; line-height: 1; opacity: 0.5; cursor: pointer;">
                    <span aria-hidden="true">×</span>
                </button>
            </div>
            <div class="modal-body" id="userModalBody">
                <div class="text-center py-4">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>