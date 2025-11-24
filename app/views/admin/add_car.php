<?php $page_title = 'Add New Car - Car Rental System'; include __DIR__ . '/partials/head.php'; ?>
        <!-- Sidebar -->
        <?php include __DIR__ . '/partials/sidebar.php'; ?>

        <!-- Main content -->
        <div class="col-md-9 ms-sm-auto col-lg-10 px-4 py-4 main-content">
            <!-- Header -->
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center border-bottom mb-4 pb-3">
                <div>
                    <h1 class="h2 mb-0">Add New Car</h1>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-0 mt-2">
                            <li class="breadcrumb-item"><a href="<?php echo site_url('/admin/dashboard'); ?>">Dashboard</a></li>
                            <li class="breadcrumb-item"><a href="<?php echo site_url('/admin/cars'); ?>">Cars</a></li>
                            <li class="breadcrumb-item active">Add New Car</li>
                        </ol>
                    </nav>
                </div>
                <div class="d-flex align-items-center">
                    <a href="<?php echo site_url('/admin/cars'); ?>" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left me-2"></i> Back to Cars
                    </a>
                    <div class="ms-3">
                        <div class="user-info text-end">
                            <p class="mb-0"><strong>Welcome, <?php echo isset($username) ? htmlspecialchars($username) : 'Admin'; ?></strong></p>
                            <small class="text-muted">Administrator</small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Alert -->
            <?php if ($this->session->flashdata('error')): ?>
                <div class="alert alert-danger d-flex align-items-center border-0 shadow-sm mb-4">
                    <i class="fas fa-exclamation-circle me-2"></i>
                    <?php echo $this->session->flashdata('error'); ?>
                </div>
            <?php endif; ?>

            <!-- Add Car Form -->
            <div class="card border-0 shadow-sm">
                <div class="card-body p-4">
                    <form action="<?php echo site_url('/admin/cars/add'); ?>" method="post" enctype="multipart/form-data">
                        <!-- Car Basic Info -->
                        <div class="row mb-4">
                            <div class="col-12 mb-3">
                                <h5 class="text-muted">Basic Information</h5>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Make *</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light"><i class="fas fa-car"></i></span>
                                    <input type="text" id="make" name="make" class="form-control" required placeholder="e.g., Toyota">
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Model *</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light"><i class="fas fa-tag"></i></span>
                                    <input type="text" id="model" name="model" class="form-control" required placeholder="e.g., Camry">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Year *</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light"><i class="fas fa-calendar"></i></span>
                                    <input type="number" id="year" name="year" class="form-control" required min="2000" max="2025" placeholder="2023">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Color *</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light"><i class="fas fa-palette"></i></span>
                                    <input type="text" id="color" name="color" class="form-control" required placeholder="e.g., Silver">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Category</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light"><i class="fas fa-list"></i></span>
                                    <select id="category" name="category" class="form-select">
                                        <option value="">Select Category (optional)</option>
                                        <option value="Sedan">Sedan</option>
                                        <option value="SUV">SUV</option>
                                        <option value="Hatchback">Hatchback</option>
                                        <option value="Van">Van</option>
                                        <option value="Truck">Truck</option>
                                        <option value="Coupe">Coupe</option>
                                        <option value="Convertible">Convertible</option>
                                        <option value="Others">Others</option>
                                    </select>
                                    <div class="form-text mt-2">
                                        Quick pick:
                                        <?php foreach (["Sedan","SUV","Hatchback","Van","Truck","Coupe","Convertible","Others"] as $cat): ?>
                                            <span class="badge bg-light text-primary border me-1 category-chip" data-value="<?= $cat ?>" style="cursor:pointer;">
                                                <i class="fas fa-plus me-1"></i><?= $cat ?>
                                            </span>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Plate Number *</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light"><i class="fas fa-id-card"></i></span>
                                    <input type="text" id="plate_number" name="plate_number" class="form-control" required placeholder="e.g., ABC-1234">
                                </div>
                            </div>
                        </div>

                        <!-- Car Details -->
                        <div class="row mb-4">
                            <div class="col-12 mb-3">
                                <h5 class="text-muted">Vehicle Details</h5>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">VIN *</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light"><i class="fas fa-barcode"></i></span>
                                    <input type="text" id="vin" name="vin" class="form-control" required placeholder="17-character VIN">
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Mileage *</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light"><i class="fas fa-tachometer-alt"></i></span>
                                    <input type="number" id="mileage" name="mileage" class="form-control" required placeholder="e.g., 15000">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Fuel Type *</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light"><i class="fas fa-gas-pump"></i></span>
                                    <select id="fuel_type" name="fuel_type" class="form-select" required>
                                        <option value="">Select Fuel Type</option>
                                        <option value="gasoline">Gasoline</option>
                                        <option value="diesel">Diesel</option>
                                        <option value="hybrid">Hybrid</option>
                                        <option value="electric">Electric</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Transmission *</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light"><i class="fas fa-cog"></i></span>
                                    <select id="transmission" name="transmission" class="form-select" required>
                                        <option value="">Select Transmission</option>
                                        <option value="automatic">Automatic</option>
                                        <option value="manual">Manual</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Seating Capacity *</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light"><i class="fas fa-users"></i></span>
                                    <input type="number" id="seating_capacity" name="seating_capacity" class="form-control" required min="2" max="9" placeholder="5">
                                </div>
                            </div>
                        </div>

                        <!-- Rental Info -->
                        <div class="row mb-4">
                            <div class="col-12 mb-3">
                                <h5 class="text-muted">Rental Information</h5>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Daily Rate (₱) *</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light"><i class="fas fa-dollar-sign"></i></span>
                                    <input type="number" id="daily_rate" name="daily_rate" class="form-control" required step="0.01" placeholder="2500.00">
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Car Image</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light"><i class="fas fa-image"></i></span>
                                    <input type="file" id="car_image" name="car_image" accept="image/*" class="form-control">
                                </div>
                                <small class="text-muted">Upload JPG/PNG/GIF up to 5MB. Optional: You can also paste a URL below.</small>
                                <input type="text" id="image_path" name="image_path" class="form-control mt-2" placeholder="https://example.com/image.jpg (optional)">
                            </div>
                        </div>

                        <!-- Description -->
                        <div class="row mb-4">
                            <div class="col-12">
                                <label class="form-label">Description</label>
                                <textarea id="description" name="description" class="form-control" rows="4" placeholder="Enter car description and notable features..."></textarea>
                                <small class="text-muted">Include important details about the car's features and condition.</small>
                            </div>
                        </div>

                        <!-- Live Preview -->
                        <div class="row mb-4">
                            <div class="col-12">
                                <div class="card border-0 shadow-sm">
                                    <div class="card-body">
                                        <div class="d-flex align-items-start">
                                            <img id="preview_img" src="https://cdn-icons-png.flaticon.com/512/743/743988.png" class="me-3" alt="preview" style="width: 120px; height: 80px; object-fit: cover; border-radius: 8px; border: 1px solid #e9ecef; background:#fff;">
                                            <div>
                                                <div class="text-muted small mb-1">Live Preview</div>
                                                <h5 class="mb-1" id="preview_title">Make Model <span class="text-muted">(Year)</span></h5>
                                                <div class="mb-2">
                                                    <span class="badge bg-secondary me-1" id="preview_category">Category</span>
                                                    <span class="badge bg-info text-dark me-1" id="preview_color">Color</span>
                                                    <span class="badge bg-light text-dark" id="preview_plate">PLATE-0000</span>
                                                </div>
                                                <div><strong id="preview_rate">₱0.00/day</strong></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Form Actions -->
                        <div class="d-flex justify-content-end gap-2">
                            <a href="<?php echo site_url('/admin/cars'); ?>" class="btn btn-outline-secondary">
                                <i class="fas fa-times me-1"></i> Cancel
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-1"></i> Add Car
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
<script>
(function(){
    const el = (id) => document.getElementById(id);
    const make = el('make');
    const model = el('model');
    const year = el('year');
    const color = el('color');
    const category = el('category');
    const plate = el('plate_number');
    const vin = el('vin');
    const rate = el('daily_rate');
    const imgFile = el('car_image');
    const imgUrl = el('image_path');

    const title = el('preview_title');
    const chipCat = el('preview_category');
    const chipColor = el('preview_color');
    const chipPlate = el('preview_plate');
    const lblRate = el('preview_rate');
    const img = el('preview_img');

    function formatPHP(n){
        const num = parseFloat(n);
        if (isNaN(num)) return '₱0.00/day';
        try { return num.toLocaleString('en-PH', {style:'currency', currency:'PHP'}) + '/day'; }
        catch(e){ return '₱' + num.toFixed(2) + '/day'; }
    }

    function updatePreview(){
        const tMake = (make?.value || '').trim();
        const tModel = (model?.value || '').trim();
        const tYear = (year?.value || '').trim();
        title.textContent = (tMake || 'Make') + ' ' + (tModel || 'Model') + (tYear ? ' (' + tYear + ')' : ' (Year)');
        chipCat.textContent = (category?.value || 'Category') || 'Category';
        chipColor.textContent = (color?.value || 'Color') || 'Color';
        const p = (plate?.value || '').toUpperCase();
        chipPlate.textContent = p || 'PLATE-0000';
        lblRate.textContent = formatPHP(rate?.value || '');
    }

    function normalizePlate(){
        if (!plate) return;
        let v = plate.value.toUpperCase();
        v = v.replace(/[^A-Z0-9\- ]/g,'');
        plate.value = v;
        updatePreview();
    }

    function normalizeVIN(){
        if (!vin) return;
        let v = vin.value.toUpperCase();
        v = v.replace(/\s+/g,'');
        vin.value = v;
    }

    function tryPreviewFromFile(file){
        if (!file) return;
        const fr = new FileReader();
        fr.onload = (e) => { img.src = e.target.result; };
        fr.readAsDataURL(file);
    }

    function tryPreviewFromUrl(){
        const url = (imgUrl?.value || '').trim();
        if (!url) return;
        img.src = url;
    }

    [make, model, year, color, category, plate, rate].forEach(elm => {
        if (elm) elm.addEventListener('input', updatePreview);
        if (elm && elm.tagName === 'SELECT') elm.addEventListener('change', updatePreview);
    });
    if (plate) plate.addEventListener('input', normalizePlate);
    if (vin) vin.addEventListener('input', normalizeVIN);
    if (imgFile) imgFile.addEventListener('change', (e)=> tryPreviewFromFile(e.target.files?.[0]));
    if (imgUrl) imgUrl.addEventListener('input', tryPreviewFromUrl);

    // Category quick chips
    document.querySelectorAll('.category-chip').forEach(chip => {
        chip.addEventListener('click', () => {
            const val = chip.getAttribute('data-value');
            if (category) { category.value = val; category.dispatchEvent(new Event('change')); }
        });
    });

    updatePreview();
})();
</script>
<?php include __DIR__ . '/partials/footer.php'; ?>
