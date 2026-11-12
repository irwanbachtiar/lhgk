<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ship Contribution Analytics - Dashboard LHGK</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-9ndCyUaIbzAi2FUVXJi0CjmCapSmO7SnpJef0486qhLnuZ2cdeRhO02iuK6FUUVM" crossorigin="anonymous">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet" integrity="sha384-QuGBSgV5Im3DzL2z+8Ko9/hqNy/N0O7zwvXAtfd1MvPKWa/UbeLV65cfm4BV5Wgq" crossorigin="anonymous">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .navbar {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        .filter-section, .content-card {
            background: white;
            padding: 20px;
            border-radius: 15px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
        .search-result-item {
            cursor: pointer;
        }
        .ship-badge {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        .timeline {
            position: relative;
            padding-left: 28px;
            margin-top: 10px;
        }
        .timeline::before {
            content: '';
            position: absolute;
            left: 7px;
            top: 4px;
            bottom: 4px;
            width: 2px;
            background: #e2e2f0;
        }
        .timeline-item {
            position: relative;
            padding-bottom: 16px;
        }
        .timeline-item:last-child {
            padding-bottom: 0;
        }
        .timeline-item::before {
            content: '';
            position: absolute;
            left: -28px;
            top: 4px;
            width: 14px;
            height: 14px;
            border-radius: 50%;
            background: #764ba2;
            border: 2px solid #fff;
            box-shadow: 0 0 0 2px #764ba2;
        }
        .branch-section + .branch-section {
            margin-top: 24px;
            border-top: 1px solid #eee;
            padding-top: 20px;
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-dark mb-4">
        <div class="container-fluid">
            <span class="navbar-brand mb-0 h1"><i class="bi bi-ship"></i> Ship Contribution Analytics</span>
            <div>
                <a href="<?php echo e(route('dashboard')); ?>" class="btn btn-light btn-sm me-2">
                    <i class="bi bi-graph-up-arrow"></i> Dashboard LHGK
                </a>
                <a href="<?php echo e(route('gps')); ?>" class="btn btn-light btn-sm me-2">
                    <i class="bi bi-geo-fill"></i> GPS
                </a>
                <a href="<?php echo e(route('summary.tunda')); ?>" class="btn btn-light btn-sm">
                    <i class="bi bi-life-preserver"></i> Summary Tunda
                </a>
            </div>
        </div>
    </nav>

    <div class="container-fluid">

        <?php if(session('error')): ?>
            <div class="alert alert-danger"><?php echo e(session('error')); ?></div>
        <?php endif; ?>

        <!-- Search Section -->
        <div class="filter-section">
            <form method="GET" action="<?php echo e(route('contribution')); ?>" class="row align-items-end g-2">
                <div class="col-md-8">
                    <label class="form-label"><i class="bi bi-search"></i> Cari Nama Kapal:</label>
                    <input type="text" name="search" class="form-control" placeholder="Ketik nama kapal, misal: KIRANA VII"
                           value="<?php echo e($search); ?>" autocomplete="off">
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="bi bi-search"></i> Cari
                    </button>
                </div>
                <div class="col-md-2">
                    <?php if($search !== '' || $selectedKapal !== ''): ?>
                        <a href="<?php echo e(route('contribution')); ?>" class="btn btn-outline-secondary w-100">
                            <i class="bi bi-x-circle"></i> Reset
                        </a>
                    <?php endif; ?>
                </div>
            </form>

            <?php if($search !== ''): ?>
                <div class="mt-3">
                    <?php if($searchResults->isEmpty()): ?>
                        <div class="alert alert-secondary mb-0">
                            <i class="bi bi-info-circle"></i> Tidak ditemukan kapal dengan nama mengandung "<strong><?php echo e($search); ?></strong>".
                        </div>
                    <?php else: ?>
                        <label class="form-label text-muted">Ditemukan <?php echo e($searchResults->count()); ?> kapal — pilih salah satu:</label>
                        <div class="list-group">
                            <?php $__currentLoopData = $searchResults; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $kapal): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <a href="<?php echo e(route('contribution', ['kapal' => $kapal])); ?>"
                                   class="list-group-item list-group-item-action search-result-item d-flex justify-content-between align-items-center <?php echo e($selectedKapal == $kapal ? 'active' : ''); ?>">
                                    <span><i class="bi bi-ship me-2"></i><?php echo e($kapal); ?></span>
                                    <i class="bi bi-chevron-right"></i>
                                </a>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>

        <?php if($selectedKapal === ''): ?>
            <div class="content-card text-center py-5">
                <i class="bi bi-ship" style="font-size: 3rem; color: #764ba2;"></i>
                <h5 class="mt-3">Cari dan pilih nama kapal untuk melihat kontribusinya</h5>
                <p class="text-muted mb-0">Analitik mencakup histori kunjungan, timeline per cabang, dan rekap kontribusi pendapatan.</p>
            </div>
        <?php else: ?>
            <!-- Ship Header + Periode Filter -->
            <div class="content-card">
                <div class="d-flex justify-content-between align-items-start flex-wrap gap-3">
                    <div>
                        <h4 class="mb-1"><i class="bi bi-ship ship-badge rounded p-2 me-2"></i><?php echo e($selectedKapal); ?></h4>
                        <?php if($shipInfo): ?>
                            <div class="text-muted">
                                <span class="badge bg-light text-dark border me-1"><?php echo e($shipInfo->JN_KAPAL ?? '-'); ?></span>
                                <?php if(!empty($shipInfo->KD_BENDERA)): ?>
                                    <span class="badge bg-light text-dark border me-1">Bendera: <?php echo e($shipInfo->KD_BENDERA); ?></span>
                                <?php endif; ?>
                                <?php if(!empty($shipInfo->KP_GRT)): ?>
                                    <span class="badge bg-light text-dark border me-1">GRT: <?php echo e(number_format((float) $shipInfo->KP_GRT, 0, ',', '.')); ?></span>
                                <?php endif; ?>
                                <?php if(!empty($shipInfo->KP_LOA)): ?>
                                    <span class="badge bg-light text-dark border me-1">LOA: <?php echo e(number_format((float) $shipInfo->KP_LOA, 0, ',', '.')); ?></span>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                        <div class="mt-2">
                            <?php $__currentLoopData = $branchList; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $branch): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <span class="badge bg-secondary me-1 mb-1" title="<?php echo e($branch); ?>"><i class="bi bi-building"></i> <?php echo e($branch); ?></span>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </div>
                    </div>
                    <a href="<?php echo e(route('export.contribution', ['kapal' => $selectedKapal, 'periode' => $selectedPeriode])); ?>"
                       class="btn btn-success btn-sm">
                        <i class="bi bi-file-earmark-excel"></i> Download Excel
                    </a>
                </div>

                <form method="GET" action="<?php echo e(route('contribution')); ?>" class="row align-items-end g-2 mt-3">
                    <input type="hidden" name="kapal" value="<?php echo e($selectedKapal); ?>">
                    <div class="col-md-4">
                        <label class="form-label"><i class="bi bi-calendar-range"></i> Periode:</label>
                        <select name="periode" class="form-select">
                            <option value="all" <?php echo e($selectedPeriode == 'all' ? 'selected' : ''); ?>>Semua Periode</option>
                            <?php $__currentLoopData = $periods; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $period): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($period); ?>" <?php echo e($selectedPeriode == $period ? 'selected' : ''); ?>><?php echo e($period); ?></option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary w-100">Terapkan</button>
                    </div>
                </form>
            </div>

            <!-- Rekap Kontribusi per Branch -->
            <div class="content-card">
                <h5 class="mb-3"><i class="bi bi-bar-chart-fill"></i> Rekap Kontribusi per Branch</h5>
                <?php if($branchContribution->isEmpty()): ?>
                    <div class="alert alert-secondary mb-0">
                        <i class="bi bi-info-circle"></i> Tidak ada data kontribusi untuk filter yang dipilih.
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover table-striped">
                            <thead class="table-dark">
                                <tr>
                                    <th>Branch</th>
                                    <th class="text-end">Jumlah Kunjungan</th>
                                    <th class="text-end">Jumlah Gerakan</th>
                                    <th class="text-end">Pendapatan Pandu</th>
                                    <th class="text-end">Pendapatan Tunda</th>
                                    <th class="text-end">Total Kontribusi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $__currentLoopData = $branchContribution; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <tr>
                                    <td><?php echo e($row->NM_BRANCH); ?></td>
                                    <td class="text-end"><?php echo e(number_format($row->jumlah_kunjungan)); ?></td>
                                    <td class="text-end"><?php echo e(number_format($row->jumlah_gerakan)); ?></td>
                                    <td class="text-end">Rp <?php echo e(number_format((float) $row->total_pandu, 0, ',', '.')); ?></td>
                                    <td class="text-end">Rp <?php echo e(number_format((float) $row->total_tunda, 0, ',', '.')); ?></td>
                                    <td class="text-end fw-bold">Rp <?php echo e(number_format((float) $row->total_kontribusi, 0, ',', '.')); ?></td>
                                </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </tbody>
                            <tfoot class="table-light">
                                <tr class="fw-bold">
                                    <td>Total</td>
                                    <td class="text-end"><?php echo e(number_format($totalContribution['jumlah_kunjungan'])); ?></td>
                                    <td class="text-end"><?php echo e(number_format($totalContribution['jumlah_gerakan'])); ?></td>
                                    <td class="text-end">Rp <?php echo e(number_format((float) $totalContribution['total_pandu'], 0, ',', '.')); ?></td>
                                    <td class="text-end">Rp <?php echo e(number_format((float) $totalContribution['total_tunda'], 0, ',', '.')); ?></td>
                                    <td class="text-end">Rp <?php echo e(number_format((float) $totalContribution['total_kontribusi'], 0, ',', '.')); ?></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Branch Visit Timeline -->
            <div class="content-card">
                <h5 class="mb-3"><i class="bi bi-clock-history"></i> Branch Visit Timeline</h5>
                <?php if($branchContribution->isEmpty()): ?>
                    <div class="alert alert-secondary mb-0">
                        <i class="bi bi-info-circle"></i> Belum ada kunjungan untuk ditampilkan.
                    </div>
                <?php else: ?>
                    <?php $__currentLoopData = $branchContribution; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $branchRow): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <?php $visits = $branchTimeline->get($branchRow->NM_BRANCH, collect()); ?>
                        <div class="branch-section">
                            <h6 class="mb-0"><i class="bi bi-building"></i> <?php echo e($branchRow->NM_BRANCH); ?>

                                <span class="text-muted small">(<?php echo e(number_format($branchRow->jumlah_kunjungan)); ?> kunjungan)</span>
                            </h6>
                            <div class="timeline">
                                <?php $__currentLoopData = $visits->take(10); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $visit): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <?php
                                        $pandu = (float) ($visit->PENDAPATAN_PANDU ?? 0);
                                        $tunda = (float) ($visit->PENDAPATAN_TUNDA ?? 0);
                                    ?>
                                    <div class="timeline-item">
                                        <div class="small text-muted"><?php echo e($visit->TGL_TIBA ?? '-'); ?> <?php echo e($visit->JAM_TIBA ?? ''); ?> &middot; Periode <?php echo e($visit->PERIODE ?? '-'); ?> &middot; PPKB <?php echo e($visit->PPKB_CODE ?? '-'); ?></div>
                                        <div>
                                            Pandu: <strong>Rp <?php echo e(number_format($pandu, 0, ',', '.')); ?></strong>
                                            &nbsp;|&nbsp; Tunda: <strong>Rp <?php echo e(number_format($tunda, 0, ',', '.')); ?></strong>
                                            &nbsp;|&nbsp; Total: <strong>Rp <?php echo e(number_format($pandu + $tunda, 0, ',', '.')); ?></strong>
                                        </div>
                                    </div>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </div>
                            <?php if($visits->count() > 10): ?>
                                <div class="small text-muted mt-1">+<?php echo e(number_format($visits->count() - 10)); ?> kunjungan lainnya — lihat selengkapnya di Histori Kunjungan.</div>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                <?php endif; ?>
            </div>

            <!-- Histori Kunjungan -->
            <div class="content-card">
                <h5 class="mb-3"><i class="bi bi-table"></i> Histori Kunjungan</h5>
                <?php if($visitHistory && $visitHistory->count() > 0): ?>
                    <div class="table-responsive">
                        <table class="table table-hover table-striped table-sm">
                            <thead class="table-dark">
                                <tr>
                                    <th>No</th>
                                    <th>Tgl Tiba</th>
                                    <th>Jam Tiba</th>
                                    <th>Periode</th>
                                    <th>Branch</th>
                                    <th>PPKB Code</th>
                                    <th>No. UKK</th>
                                    <th class="text-end">Pendapatan Pandu</th>
                                    <th class="text-end">Pendapatan Tunda</th>
                                    <th class="text-end">Total</th>
                                    <th>Status Nota</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $__currentLoopData = $visitHistory; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <?php
                                        $pandu = (float) ($row->PENDAPATAN_PANDU ?? 0);
                                        $tunda = (float) ($row->PENDAPATAN_TUNDA ?? 0);
                                    ?>
                                    <tr>
                                        <td><?php echo e($visitHistory->firstItem() + $index); ?></td>
                                        <td class="text-nowrap small"><?php echo e($row->TGL_TIBA ?? '-'); ?></td>
                                        <td class="text-nowrap small"><?php echo e($row->JAM_TIBA ?? '-'); ?></td>
                                        <td class="text-nowrap small"><?php echo e($row->PERIODE ?? '-'); ?></td>
                                        <td class="text-nowrap small"><?php echo e($row->NM_BRANCH ?? '-'); ?></td>
                                        <td class="text-nowrap small"><?php echo e($row->PPKB_CODE ?? '-'); ?></td>
                                        <td class="text-nowrap small"><?php echo e($row->NO_UKK ?? '-'); ?></td>
                                        <td class="text-end text-nowrap small">Rp <?php echo e(number_format($pandu, 0, ',', '.')); ?></td>
                                        <td class="text-end text-nowrap small">Rp <?php echo e(number_format($tunda, 0, ',', '.')); ?></td>
                                        <td class="text-end text-nowrap small fw-bold">Rp <?php echo e(number_format($pandu + $tunda, 0, ',', '.')); ?></td>
                                        <td class="text-nowrap small"><?php echo e($row->STATUS_NOTA ?? '-'); ?></td>
                                    </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </tbody>
                        </table>
                    </div>

                    <?php if($visitHistory->hasPages()): ?>
                        <div class="d-flex justify-content-between align-items-center mt-3">
                            <div class="text-muted">
                                Menampilkan <?php echo e($visitHistory->firstItem()); ?> - <?php echo e($visitHistory->lastItem()); ?> dari <?php echo e($visitHistory->total()); ?> kunjungan
                            </div>
                            <div>
                                <?php echo e($visitHistory->links('pagination::bootstrap-5')); ?>

                            </div>
                        </div>
                    <?php endif; ?>
                <?php else: ?>
                    <div class="alert alert-secondary mb-0">
                        <i class="bi bi-info-circle"></i> Tidak ada histori kunjungan untuk filter yang dipilih.
                    </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
<?php /**PATH /Users/irwan/Downloads/project ai/lhgk/resources/views/contribution.blade.php ENDPATH**/ ?>