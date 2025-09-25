<?php
require_once 'includes/data/db.php';
require_once 'includes/functions/simple_auth.php';

$auth = new SimpleAuth();
if (!$auth->isLoggedIn() || !$auth->hasPermission('view_all_data')) {
    header('Location: auth/login.php');
    exit;
}

$current_user = $auth->getCurrentUser();
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Reports</h1>
</div>

<div class="row">
    <div class="col-12">
        <div class="card shadow">
            <div class="card-header">
                <h5 class="card-title mb-0">System Reports</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <div class="card">
                            <div class="card-body text-center">
                                <i class="fe fe-users fe-48 text-primary mb-3"></i>
                                <h5>Employee Reports</h5>
                                <p class="text-muted">Generate employee performance and competency reports</p>
                                <button class="btn btn-primary">Generate</button>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <div class="card">
                            <div class="card-body text-center">
                                <i class="fe fe-book-open fe-48 text-success mb-3"></i>
                                <h5>Training Reports</h5>
                                <p class="text-muted">Generate training completion and effectiveness reports</p>
                                <button class="btn btn-success">Generate</button>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <div class="card">
                            <div class="card-body text-center">
                                <i class="fe fe-trending-up fe-48 text-info mb-3"></i>
                                <h5>Succession Reports</h5>
                                <p class="text-muted">Generate succession planning and readiness reports</p>
                                <button class="btn btn-info">Generate</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>






