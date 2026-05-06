<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Summary LHGK</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-9ndCyUaIbzAi2FUVXJi0CjmCapSmO7SnpJef0486qhLnuZ2cdeRhO02iuK6FUUVM" crossorigin="anonymous">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet" integrity="sha384-QuGBSgV5Im3DzL2z+8Ko9/hqNy/N0O7zwvXAtfd1MvPKWa/UbeLV65cfm4BV5Wgq" crossorigin="anonymous">
    <style>
        .stat-card {
            border: none;
            border-radius: 8px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
            margin-bottom: 20px;
        }

        .stat-card:hover {
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        }

        .wilayah-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 15px 20px;
            border-radius: 8px;
            margin-top: 25px;
            margin-bottom: 15px;
            font-weight: 600;
            font-size: 1.1rem;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: space-between;
            user-select: none;
            transition: all 0.3s ease;
        }

        .wilayah-header:hover {
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.25);
        }

        .wilayah-toggle-icon {
            transition: transform 0.3s ease;
            font-size: 1.3rem;
        }

        .wilayah-toggle-icon.collapsed {
            transform: rotate(-90deg);
        }

        .wilayah-content {
            display: none;
        }

        .wilayah-content.show {
            display: block;
        }

        .branch-row {
            background: white;
            border: 1px solid #e5e7eb;
            border-radius: 6px;
            padding: 15px;
            margin-bottom: 15px;
        }

        .branch-name {
            font-size: 1.05rem;
            font-weight: 600;
            color: #1f2937;
            margin-bottom: 12px;
            border-bottom: 2px solid #f3f4f6;
            padding-bottom: 8px;
        }

        .metrics-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 12px;
            margin-top: 10px;
        }

        .metric-item {
            background: #f9fafb;
            padding: 10px;
            border-radius: 4px;
            border-left: 3px solid #667eea;
        }

        .metric-label {
            font-size: 0.8rem;
            color: #6b7280;
            font-weight: 500;
            margin-bottom: 4px;
        }

        .metric-value {
            font-size: 1.3rem;
            font-weight: 700;
            color: #1f2937;
        }

        .metric-value.highlight {
            color: #667eea;
        }

        .badge-realisasi {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 6px 12px;
            border-radius: 4px;
            font-size: 0.85rem;
            background-color: #f3f4f6;
            border-left: 3px solid #3b82f6;
        }

        .realisasi-group {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin: 8px 0;
        }

        .section-divider {
            height: 1px;
            background: #e5e7eb;
            margin: 12px 0;
        }

        .global-loading-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.7);
            z-index: 9999;
            align-items: center;
            justify-content: center;
        }

        .global-loading-content {
            background: white;
            padding: 30px 50px;
            border-radius: 8px;
            text-align: center;
        }

        .global-loading-spinner {
            border: 4px solid #f3f4f6;
            border-top: 4px solid #667eea;
            border-radius: 50%;
            width: 40px;
            height: 40px;
            animation: spin 1s linear infinite;
            margin: 0 auto 15px;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        .global-loading-text {
            color: #667eea;
            font-weight: 600;
            margin: 0;
        }
    </style>
</head>
<body>
    <!-- Global Loading Overlay -->
    <div id="globalLoading" class="global-loading-overlay">
        <div class="global-loading-content">
            <div class="global-loading-spinner"></div>
            <p class="global-loading-text" id="loadingText">Memproses data...</p>
        </div>
    </div>

    <nav class="navbar navbar-dark mb-4">
        <div class="container-fluid">
            <span class="navbar-brand mb-0 h1"><i class="bi bi-graph-up-arrow"></i> Summary LHGK</span>
            <div>
                <a href="<?php echo e(route('dashboard')); ?>" class="btn btn-light btn-sm me-2">
                    <i class="bi bi-speedometer2"></i> Dashboard
                </a>
                <a href="<?php echo e(route('trafik')); ?>" class="btn btn-light btn-sm me-2">
                    <i class="bi bi-ship"></i> Trafik
                </a>
                <a href="<?php echo e(route('monitoring.nota')); ?>" class="btn btn-light btn-sm me-2">
                    <i class="bi bi-file-earmark-check"></i> Monitoring Nota
                </a>
            </div>
        </div>
    </nav>

    <div class="container-fluid">
        <!-- Period Filter -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card stat-card">
                    <div class="card-body">
                        <h5 class="card-title mb-3"><i class="bi bi-funnel"></i> Filter Periode</h5>
                        <form method="GET" action="<?php echo e(route('summary.lhgk')); ?>">
                            <div class="row align-items-end">
                                <div class="col-md-4">
                                    <label for="periode" class="form-label"><strong>Pilih Periode:</strong></label>
                                    <select id="periode" name="periode" class="form-select" onchange="document.querySelector('form').submit();">
                                        <option value="all" <?php echo e($selectedPeriode == 'all' ? 'selected' : ''); ?>>-- Semua Periode --</option>
                                        <?php $__currentLoopData = $periods; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $periode): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <option value="<?php echo e($periode); ?>" <?php echo e($selectedPeriode == $periode ? 'selected' : ''); ?>>
                                                <?php echo e($periode); ?>

                                            </option>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    </select>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <?php if($selectedPeriode == 'all'): ?>
            <div class="row">
                <div class="col-12">
                    <div class="alert alert-info text-center">
                        <i class="bi bi-info-circle"></i> Silahkan pilih periode untuk menampilkan data
                    </div>
                </div>
            </div>
        <?php elseif($branchSummary->isEmpty()): ?>
            <div class="row">
                <div class="col-12">
                    <div class="alert alert-warning text-center">
                        <i class="bi bi-exclamation-triangle"></i> Tidak ada data tersedia untuk periode <?php echo e($selectedPeriode); ?>

                    </div>
                </div>
            </div>
        <?php else: ?>
            <!-- Display by Wilayah -->
            <?php $__currentLoopData = $groupedBranches; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $wilayah => $branches): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <div class="wilayah-header" data-wilayah="<?php echo e(preg_replace('/[^a-zA-Z0-9]/', '', $wilayah)); ?>" onclick="toggleWilayah(this)">
                    <span>
                        <i class="bi bi-geo-alt-fill"></i> <?php echo e($wilayah); ?> (<?php echo e($branches->count()); ?> Branch)
                    </span>
                    <i class="bi bi-chevron-down wilayah-toggle-icon collapsed"></i>
                </div>

                <div class="wilayah-content" id="wilayah-<?php echo e(preg_replace('/[^a-zA-Z0-9]/', '', $wilayah)); ?>">
                    <?php $__currentLoopData = $branches; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $branch): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <div class="branch-row">
                        <?php
                            $colors = ['#4f46e5', '#059669', '#ea580c', '#e11d48', '#7c3aed', '#2563eb', '#0891b2', '#c026d3', '#b45309', '#1d4ed8'];
                            $colorIndex = abs(crc32($branch->NM_BRANCH)) % count($colors);
                            $branchColor = $colors[$colorIndex];
                        ?>
                        <div class="branch-name" style="color: <?php echo e($branchColor); ?>; border-bottom-color: <?php echo e($branchColor); ?>40;">
                            <i class="bi bi-building-fill" style="color: <?php echo e($branchColor); ?>; margin-right: 6px;"></i><?php echo e(str_replace('REGIONAL 1', 'Unit', $branch->NM_BRANCH)); ?>

                        </div>

                        <div class="metrics-grid">
                            <div class="metric-item">
                                <div class="metric-label">Jumlah GERAKAN</div>
                                <div class="metric-value highlight"><?php echo e(number_format($branch->jumlah_gerakan)); ?></div>
                            </div>
                            <div class="metric-item">
                                <div class="metric-label">Kapal Pandu</div>
                                <div class="metric-value"><?php echo e(number_format($branch->kapal_pandu)); ?></div>
                            </div>
                            <div class="metric-item">
                                <div class="metric-label">Kapal Tunda</div>
                                <div class="metric-value"><?php echo e(number_format($branch->kapal_tunda)); ?></div>
                            </div>
                            <div class="metric-item">
                                <div class="metric-label">Personil Pandu</div>
                                <div class="metric-value"><?php echo e(number_format($branch->personil_pandu)); ?></div>
                            </div>
                        </div>

                        <!-- Realisasi Pemanduan -->
                        <div class="section-divider"></div>
                        <div style="margin-top: 12px;">
                            <div style="font-weight: 600; color: #1f2937; font-size: 0.95rem; margin-bottom: 8px;">
                                <i class="bi bi-arrow-right-circle"></i> Realisasi Pemanduan
                            </div>
                            <?php
                                $wP = $branch->realisasi_pandu->web ?? 0;
                                $mP = $branch->realisasi_pandu->mobile ?? 0;
                                $pP = $branch->realisasi_pandu->partial ?? 0;
                                $tP = $wP + $mP + $pP;
                                $wPPct = $tP > 0 ? round(($wP / $tP) * 100) : 0;
                                $mPPct = $tP > 0 ? round(($mP / $tP) * 100) : 0;
                                $pPPct = $tP > 0 ? round(($pP / $tP) * 100) : 0;
                            ?>
                            <div class="realisasi-group">
                                <span class="badge-realisasi" style="background-color: #dbeafe; border-left-color: #3b82f6;">
                                    <i class="bi bi-laptop"></i> Web: <strong><?php echo e(number_format($wP)); ?></strong> (<?php echo e($wPPct); ?>%)
                                </span>
                                <span class="badge-realisasi" style="background-color: #dbeafe; border-left-color: #3b82f6;">
                                    <i class="bi bi-phone"></i> Mobile: <strong><?php echo e(number_format($mP)); ?></strong> (<?php echo e($mPPct); ?>%)
                                </span>
                                <span class="badge-realisasi" style="background-color: #dbeafe; border-left-color: #3b82f6;">
                                    <i class="bi bi-puzzle"></i> Partial: <strong><?php echo e(number_format($pP)); ?></strong> (<?php echo e($pPPct); ?>%)
                                </span>
                            </div>
                            <?php if($tP > 0): ?>
                            <div class="progress mt-2" style="height: 6px; border-radius: 3px; background-color: #e5e7eb;">
                                <div class="progress-bar bg-primary" role="progressbar" style="width: <?php echo e($wPPct); ?>%" title="Web: <?php echo e($wPPct); ?>%"></div>
                                <div class="progress-bar bg-info" role="progressbar" style="width: <?php echo e($mPPct); ?>%" title="Mobile: <?php echo e($mPPct); ?>%"></div>
                                <div class="progress-bar bg-secondary" role="progressbar" style="width: <?php echo e($pPPct); ?>%" title="Partial: <?php echo e($pPPct); ?>%"></div>
                            </div>
                            <?php endif; ?>
                        </div>

                        <!-- Realisasi Penundaan -->
                        <div style="margin-top: 12px;">
                            <div style="font-weight: 600; color: #1f2937; font-size: 0.95rem; margin-bottom: 8px;">
                                <i class="bi bi-arrow-left-circle"></i> Realisasi Penundaan
                            </div>
                            <?php
                                $wT = $branch->realisasi_tunda->web ?? 0;
                                $mT = $branch->realisasi_tunda->mobile ?? 0;
                                $pT = $branch->realisasi_tunda->partial ?? 0;
                                $tT = $wT + $mT + $pT;
                                $wTPct = $tT > 0 ? round(($wT / $tT) * 100) : 0;
                                $mTPct = $tT > 0 ? round(($mT / $tT) * 100) : 0;
                                $pTPct = $tT > 0 ? round(($pT / $tT) * 100) : 0;
                            ?>
                            <div class="realisasi-group">
                                <span class="badge-realisasi" style="background-color: #dcfce7; border-left-color: #10b981;">
                                    <i class="bi bi-laptop"></i> Web: <strong><?php echo e(number_format($wT)); ?></strong> (<?php echo e($wTPct); ?>%)
                                </span>
                                <span class="badge-realisasi" style="background-color: #dcfce7; border-left-color: #10b981;">
                                    <i class="bi bi-phone"></i> Mobile: <strong><?php echo e(number_format($mT)); ?></strong> (<?php echo e($mTPct); ?>%)
                                </span>
                                <span class="badge-realisasi" style="background-color: #dcfce7; border-left-color: #10b981;">
                                    <i class="bi bi-puzzle"></i> Partial: <strong><?php echo e(number_format($pT)); ?></strong> (<?php echo e($pTPct); ?>%)
                                </span>
                            </div>
                            <?php if($tT > 0): ?>
                            <div class="progress mt-2" style="height: 6px; border-radius: 3px; background-color: #e5e7eb;">
                                <div class="progress-bar bg-success" role="progressbar" style="width: <?php echo e($wTPct); ?>%" title="Web: <?php echo e($wTPct); ?>%"></div>
                                <div class="progress-bar" role="progressbar" style="background-color: #20c997; width: <?php echo e($mTPct); ?>%" title="Mobile: <?php echo e($mTPct); ?>%"></div>
                                <div class="progress-bar bg-secondary" role="progressbar" style="width: <?php echo e($pTPct); ?>%" title="Partial: <?php echo e($pTPct); ?>%"></div>
                            </div>
                            <?php endif; ?>
                        </div>

                        <!-- Nota Data -->
                        <div class="section-divider"></div>
                        <div style="margin-top: 12px;">
                            <div style="font-weight: 600; color: #1f2937; font-size: 0.95rem; margin-bottom: 8px;">
                                <i class="bi bi-file-earmark-check"></i> Data Nota
                            </div>
                            <div class="metrics-grid">
                                <div class="metric-item">
                                    <div class="metric-label">Nota Terbit</div>
                                    <div class="metric-value" style="color: #10b981;"><?php echo e(number_format($branch->nota_data->terbit ?? 0)); ?></div>
                                </div>
                                <div class="metric-item">
                                    <div class="metric-label">Nota Batal</div>
                                    <div class="metric-value" style="color: #ef4444;"><?php echo e(number_format($branch->nota_data->batal ?? 0)); ?></div>
                                </div>
                                <div class="metric-item">
                                    <div class="metric-label">Belum Verifikasi</div>
                                    <div class="metric-value" style="color: #f59e0b;"><?php echo e(number_format($branch->nota_data->belum_verifikasi ?? 0)); ?></div>
                                </div>
                                <div class="metric-item">
                                    <div class="metric-label">Kecepatan Terbit (hari)</div>
                                    <div class="metric-value highlight"><?php echo e(number_format($branch->nota_data->kecepatan_terbit ?? 0, 1)); ?></div>
                                </div>
                            </div>
                        </div>

                        <!-- Issue Data -->
                        <div class="section-divider"></div>
                        <div style="margin-top: 12px;">
                            <div style="font-weight: 600; color: #1f2937; font-size: 0.95rem; margin-bottom: 8px;">
                                <i class="bi bi-exclamation-triangle"></i> Data Masalah
                            </div>
                            <div class="metrics-grid">
                                <div class="metric-item">
                                    <div class="metric-label">Invoice > 2 Hari</div>
                                    <div class="metric-value" style="color: <?php echo e($branch->invoice_lebih_2_hari > 0 ? '#dc2626' : '#6b7280'); ?>;"><?php echo e(number_format($branch->invoice_lebih_2_hari)); ?></div>
                                </div>
                                <div class="metric-item">
                                    <div class="metric-label">Status Nota Pending</div>
                                    <div class="metric-value" style="color: <?php echo e($branch->status_nota > 0 ? '#f59e0b' : '#6b7280'); ?>;"><?php echo e(number_format($branch->status_nota)); ?></div>
                                </div>
                                <div class="metric-item">
                                    <div class="metric-label">PPKB Backdate</div>
                                    <div class="metric-value" style="color: <?php echo e($branch->backdate > 0 ? '#dc2626' : '#6b7280'); ?>;"><?php echo e(number_format($branch->backdate)); ?></div>
                                </div>
                                <div class="metric-item">
                                    <div class="metric-label">WT > 30 Menit</div>
                                    <div class="metric-value" style="color: <?php echo e($branch->waiting_time_over_30 > 0 ? '#f59e0b' : '#6b7280'); ?>;"><?php echo e(number_format($branch->waiting_time_over_30)); ?></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <?php if(!$loop->last): ?>
                    <div style="height: 2px; background-color: #e5e7eb; margin: 30px 10px 35px 10px; position: relative; border-radius: 2px;">
                        <div style="position: absolute; top: -14px; left: 50%; transform: translateX(-50%); background: #f8f9fa; padding: 0 15px; color: #cbd5e1; font-size: 1.2rem;">
                            <i class="bi bi-scissors"></i>
                        </div>
                    </div>
                    <?php endif; ?>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js" integrity="sha384-geWF76RCwLtnZ8qwWowPQNguL3RmwHVBC9FhGdlKrxdiJJigb/j/68SIy3Te4Bkz" crossorigin="anonymous"></script>
    <script>
        function showGlobalLoading(message = 'Memproses data...') {
            const overlay = document.getElementById('globalLoading');
            const text = document.getElementById('loadingText');
            if (overlay) {
                if (text) text.textContent = message;
                overlay.style.display = 'flex';
                document.body.style.overflow = 'hidden';
            }
        }
        
        function hideGlobalLoading() {
            const overlay = document.getElementById('globalLoading');
            if (overlay) {
                overlay.style.display = 'none';
                document.body.style.overflow = '';
            }
        }

        function toggleWilayah(headerElement) {
            const wilayahId = headerElement.getAttribute('data-wilayah');
            const contentElement = document.getElementById('wilayah-' + wilayahId);
            const toggleIcon = headerElement.querySelector('.wilayah-toggle-icon');
            
            if (contentElement) {
                contentElement.classList.toggle('show');
                toggleIcon.classList.toggle('collapsed');
                
                // Save state to localStorage
                const isExpanded = contentElement.classList.contains('show');
                localStorage.setItem('wilayah-' + wilayahId, isExpanded ? 'expanded' : 'collapsed');
            }
        }
        
        document.addEventListener('DOMContentLoaded', function() {
            // Setup form submit handlers
            const forms = document.querySelectorAll('form');
            forms.forEach(form => {
                form.addEventListener('submit', function() {
                    showGlobalLoading('Memproses permintaan...');
                });
            });

            // Restore wilayah states from localStorage
            const wilayahHeaders = document.querySelectorAll('.wilayah-header');
            wilayahHeaders.forEach(header => {
                const wilayahId = header.getAttribute('data-wilayah');
                const savedState = localStorage.getItem('wilayah-' + wilayahId);
                
                if (savedState === 'expanded') {
                    const contentElement = document.getElementById('wilayah-' + wilayahId);
                    const toggleIcon = header.querySelector('.wilayah-toggle-icon');
                    if (contentElement) {
                        contentElement.classList.add('show');
                        toggleIcon.classList.remove('collapsed');
                    }
                }
            });
        });
    </script>
</body>
</html>
<?php /**PATH D:\project ai\lhgk\resources\views/summary-lhgk.blade.php ENDPATH**/ ?>