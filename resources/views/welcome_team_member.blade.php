@extends('layouts.team_member_layout')

@section('title', 'Stock fri')

@section('content')

    <div class="col-md-12">
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col-4">
                    <h4 class="page-title">
                        Commande en attente<span class="count-title">123</span>
                    </h4>
                </div>
                <div class="col-8 text-end">
                    <div class="head-icons">
                        <a href="campaign.html" data-bs-toggle="tooltip" data-bs-placement="top"
                            data-bs-original-title="Refresh">
                            <i class="ti ti-refresh-dot"></i>
                        </a>
                        <a href="javascript:void(0);" data-bs-toggle="tooltip" data-bs-placement="top"
                            data-bs-original-title="Collapse" id="collapse-header">
                            <i class="ti ti-chevrons-up"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-xl-3 col-lg-6">
                <div class="campaign-box bg-danger-light">
                    <div class="campaign-img">
                        <span>
                            <i class="ti ti-brand-campaignmonitor"></i>
                        </span>
                        <p>Clients</p>
                    </div>
                    <h2>474</h2>
                </div>
            </div>
            <div class="col-xl-3 col-lg-6">
                <div class="campaign-box bg-warning-light">
                    <div class="campaign-img">
                        <span>
                            <i class="ti ti-send"></i>
                        </span>
                        <p>Equipe</p>
                    </div>
                    <h2>454</h2>
                </div>
            </div>
            <div class="col-xl-3 col-lg-6">
                <div class="campaign-box bg-purple-light">
                    <div class="campaign-img">
                        <span>
                            <i class="ti ti-brand-feedly"></i>
                        </span>
                        <p>Coequipiers</p>
                    </div>
                    <h2>658</h2>
                </div>
            </div>
            <div class="col-xl-3 col-lg-6">
                <div class="campaign-box bg-success-light">
                    <div class="campaign-img">
                        <span>
                            <i class="ti ti-brand-pocket"></i>
                        </span>
                        <p>Business</p>
                    </div>
                    <h2>747</h2>
                </div>
            </div>
        </div>

        <div class="campaign-tab">
            <ul class="nav">
                <li>
                    <a href="campaign.html" class="active">Active Campaign<span>24</span></a>
                </li>
                <li>
                    <a href="campaign-complete.html">Completed Campaign</a>
                </li>
                <li>
                    <a href="campaign-archieve.html">Archived Campaign</a>
                </li>
            </ul>
        </div>

        <div class="card">
            <div class="card-header">
                <div class="row align-items-center">
                    <div class="col-sm-4">
                        <div class="icon-form mb-3 mb-sm-0">
                            <span class="form-icon"><i class="ti ti-search"></i></span>
                            <input type="text" class="form-control" placeholder="Search Campaign" />
                        </div>
                    </div>
                    <div class="col-sm-8">
                        <div class="d-flex align-items-center flex-wrap row-gap-2 justify-content-sm-end">
                            <div class="dropdown me-2">
                                <a href="javascript:void(0);" class="dropdown-toggle" data-bs-toggle="dropdown"><i
                                        class="ti ti-package-export me-2"></i>Export</a>
                                <div class="dropdown-menu dropdown-menu-end">
                                    <ul>
                                        <li>
                                            <a href="javascript:void(0);" class="dropdown-item"><i
                                                    class="ti ti-file-type-pdf text-danger me-1"></i>Export as PDF</a>
                                        </li>
                                        <li>
                                            <a href="javascript:void(0);" class="dropdown-item"><i
                                                    class="ti ti-file-type-xls text-green me-1"></i>Export as Excel
                                            </a>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                            <a href="javascript:void(0);" class="btn btn-primary" data-bs-toggle="offcanvas"
                                data-bs-target="#offcanvas_add"><i class="ti ti-square-rounded-plus me-2"></i>Add New
                                Campaign</a>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between flex-wrap row-gap-2 mb-4">
                    <div class="d-flex align-items-center flex-wrap row-gap-2">
                        <div class="dropdown me-2">
                            <a href="javascript:void(0);" class="dropdown-toggle" data-bs-toggle="dropdown"><i
                                    class="ti ti-sort-ascending-2 me-2"></i>Sort
                            </a>
                            <div class="dropdown-menu dropdown-menu-start">
                                <ul>
                                    <li>
                                        <a href="javascript:void(0);" class="dropdown-item">
                                            <i class="ti ti-circle-chevron-right me-1"></i>Ascending
                                        </a>
                                    </li>
                                    <li>
                                        <a href="javascript:void(0);" class="dropdown-item">
                                            <i class="ti ti-circle-chevron-right me-1"></i>Descending
                                        </a>
                                    </li>
                                    <li>
                                        <a href="javascript:void(0);" class="dropdown-item">
                                            <i class="ti ti-circle-chevron-right me-1"></i>Recently Viewed
                                        </a>
                                    </li>
                                    <li>
                                        <a href="javascript:void(0);" class="dropdown-item">
                                            <i class="ti ti-circle-chevron-right me-1"></i>Recently Added
                                        </a>
                                    </li>
                                </ul>
                            </div>
                        </div>
                        <div class="icon-form">
                            <span class="form-icon"><i class="ti ti-calendar"></i></span>
                            <input type="text" class="form-control bookingrange" placeholder />
                        </div>
                    </div>
                    <div class="d-flex align-items-center flex-wrap row-gap-2">
                        <div class="dropdown me-2">
                            <a href="javascript:void(0);" class="btn bg-soft-purple text-purple" data-bs-toggle="dropdown"
                                data-bs-auto-close="outside"><i class="ti ti-columns-3 me-2"></i>Manage Columns</a>
                            <div class="dropdown-menu dropdown-menu-md-end dropdown-md p-3">
                                <h4 class="mb-2 fw-semibold">
                                    Want to manage datatables?
                                </h4>
                                <p class="mb-3">
                                    Please drag and drop your column to reorder your
                                    table and enable see option as you want.
                                </p>
                                <div class="border-top pt-3">
                                    <div class="d-flex align-items-center justify-content-between mb-3">
                                        <p class="mb-0 d-flex align-items-center">
                                            <i class="ti ti-grip-vertical me-2"></i>Name
                                        </p>
                                        <div class="status-toggle">
                                            <input type="checkbox" id="col-name" class="check" />
                                            <label for="col-name" class="checktoggle"></label>
                                        </div>
                                    </div>
                                    <div class="d-flex align-items-center justify-content-between mb-3">
                                        <p class="mb-0 d-flex align-items-center">
                                            <i class="ti ti-grip-vertical me-2"></i>Type
                                        </p>
                                        <div class="status-toggle">
                                            <input type="checkbox" id="col-phone" class="check" />
                                            <label for="col-phone" class="checktoggle"></label>
                                        </div>
                                    </div>
                                    <div class="d-flex align-items-center justify-content-between mb-3">
                                        <p class="mb-0 d-flex align-items-center">
                                            <i class="ti ti-grip-vertical me-2"></i>Progress
                                        </p>
                                        <div class="status-toggle">
                                            <input type="checkbox" id="col-email" class="check" />
                                            <label for="col-email" class="checktoggle"></label>
                                        </div>
                                    </div>
                                    <div class="d-flex align-items-center justify-content-between mb-3">
                                        <p class="mb-0 d-flex align-items-center">
                                            <i class="ti ti-grip-vertical me-2"></i>Members
                                        </p>
                                        <div class="status-toggle">
                                            <input type="checkbox" id="col-tag" class="check" />
                                            <label for="col-tag" class="checktoggle"></label>
                                        </div>
                                    </div>
                                    <div class="d-flex align-items-center justify-content-between mb-3">
                                        <p class="mb-0 d-flex align-items-center">
                                            <i class="ti ti-grip-vertical me-2"></i>Start
                                            Date
                                        </p>
                                        <div class="status-toggle">
                                            <input type="checkbox" id="col-loc" class="check" />
                                            <label for="col-loc" class="checktoggle"></label>
                                        </div>
                                    </div>
                                    <div class="d-flex align-items-center justify-content-between mb-3">
                                        <p class="mb-0 d-flex align-items-center">
                                            <i class="ti ti-grip-vertical me-2"></i>End Date
                                        </p>
                                        <div class="status-toggle">
                                            <input type="checkbox" id="col-rate" class="check" />
                                            <label for="col-rate" class="checktoggle"></label>
                                        </div>
                                    </div>
                                    <div class="d-flex align-items-center justify-content-between mb-3">
                                        <p class="mb-0 d-flex align-items-center">
                                            <i class="ti ti-grip-vertical me-2"></i>Status
                                        </p>
                                        <div class="status-toggle">
                                            <input type="checkbox" id="col-owner" class="check" />
                                            <label for="col-owner" class="checktoggle"></label>
                                        </div>
                                    </div>
                                    <div class="d-flex align-items-center justify-content-between mb-3">
                                        <p class="mb-0 d-flex align-items-center">
                                            <i class="ti ti-grip-vertical me-2"></i>Created
                                        </p>
                                        <div class="status-toggle">
                                            <input type="checkbox" id="col-contact" class="check" checked />
                                            <label for="col-contact" class="checktoggle"></label>
                                        </div>
                                    </div>
                                    <div class="d-flex align-items-center justify-content-between">
                                        <p class="mb-0 d-flex align-items-center">
                                            <i class="ti ti-grip-vertical me-2"></i>Action
                                        </p>
                                        <div class="status-toggle">
                                            <input type="checkbox" id="col-action" class="check" />
                                            <label for="col-action" class="checktoggle"></label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="form-sorts dropdown">
                            <a href="javascript:void(0);" data-bs-toggle="dropdown" data-bs-auto-close="outside"><i
                                    class="ti ti-filter-share"></i>Filter</a>
                            <div class="filter-dropdown-menu dropdown-menu dropdown-menu-md-end p-3">
                                <div class="filter-set-view">
                                    <div class="filter-set-head">
                                        <h4><i class="ti ti-filter-share"></i>Filter</h4>
                                    </div>
                                    <div class="accordion" id="accordionExample">
                                        <div class="filter-set-content">
                                            <div class="filter-set-content-head">
                                                <a href="#" data-bs-toggle="collapse" data-bs-target="#collapseTwo"
                                                    aria-expanded="true" aria-controls="collapseTwo">Name</a>
                                            </div>
                                            <div class="filter-set-contents accordion-collapse collapse show"
                                                id="collapseTwo" data-bs-parent="#accordionExample">
                                                <div class="filter-content-list">
                                                    <div class="mb-2 icon-form">
                                                        <span class="form-icon"><i class="ti ti-search"></i></span>
                                                        <input type="text" class="form-control"
                                                            placeholder="Search Name" />
                                                    </div>
                                                    <ul>
                                                        <li>
                                                            <div class="filter-checks">
                                                                <label class="checkboxs">
                                                                    <input type="checkbox" />
                                                                    <span class="checkmarks"></span>
                                                                    Distribution
                                                                </label>
                                                            </div>
                                                        </li>
                                                        <li>
                                                            <div class="filter-checks">
                                                                <label class="checkboxs">
                                                                    <input type="checkbox" />
                                                                    <span class="checkmarks"></span>
                                                                    Merchandising
                                                                </label>
                                                            </div>
                                                        </li>
                                                        <li>
                                                            <div class="filter-checks">
                                                                <label class="checkboxs">
                                                                    <input type="checkbox" />
                                                                    <span class="checkmarks"></span>
                                                                    Pricing
                                                                </label>
                                                            </div>
                                                        </li>
                                                        <li>
                                                            <div class="filter-checks">
                                                                <label class="checkboxs">
                                                                    <input type="checkbox" />
                                                                    <span class="checkmarks"></span>
                                                                    Increased sales
                                                                </label>
                                                            </div>
                                                        </li>
                                                        <li>
                                                            <div class="filter-checks">
                                                                <label class="checkboxs">
                                                                    <input type="checkbox" />
                                                                    <span class="checkmarks"></span>
                                                                    Brand recognition
                                                                </label>
                                                            </div>
                                                        </li>
                                                    </ul>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="filter-set-content">
                                            <div class="filter-set-content-head">
                                                <a href="#" class="collapsed" data-bs-toggle="collapse"
                                                    data-bs-target="#owner" aria-expanded="false"
                                                    aria-controls="owner">Type</a>
                                            </div>
                                            <div class="filter-set-contents accordion-collapse collapse" id="owner"
                                                data-bs-parent="#accordionExample">
                                                <div class="filter-content-list">
                                                    <ul>
                                                        <li>
                                                            <div class="filter-checks">
                                                                <label class="checkboxs">
                                                                    <input type="checkbox" checked />
                                                                    <span class="checkmarks"></span>
                                                                    Public Relations
                                                                </label>
                                                            </div>
                                                        </li>
                                                        <li>
                                                            <div class="filter-checks">
                                                                <label class="checkboxs">
                                                                    <input type="checkbox" />
                                                                    <span class="checkmarks"></span>
                                                                    Content Marketing
                                                                </label>
                                                            </div>
                                                        </li>
                                                        <li>
                                                            <div class="filter-checks">
                                                                <label class="checkboxs">
                                                                    <input type="checkbox" />
                                                                    <span class="checkmarks"></span>
                                                                    Brand
                                                                </label>
                                                            </div>
                                                        </li>
                                                    </ul>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="filter-set-content">
                                            <div class="filter-set-content-head">
                                                <a href="#" class="collapsed" data-bs-toggle="collapse"
                                                    data-bs-target="#Status" aria-expanded="false"
                                                    aria-controls="Status">Start Date</a>
                                            </div>
                                            <div class="filter-set-contents accordion-collapse collapse" id="Status"
                                                data-bs-parent="#accordionExample">
                                                <div class="filter-content-list">
                                                    <ul>
                                                        <li>
                                                            <div class="filter-checks">
                                                                <label class="checkboxs">
                                                                    <input type="checkbox" checked />
                                                                    <span class="checkmarks"></span>
                                                                    25 Sep 2023
                                                                </label>
                                                            </div>
                                                        </li>
                                                        <li>
                                                            <div class="filter-checks">
                                                                <label class="checkboxs">
                                                                    <input type="checkbox" />
                                                                    <span class="checkmarks"></span>
                                                                    29 Sep 2023
                                                                </label>
                                                            </div>
                                                        </li>
                                                    </ul>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="filter-set-content">
                                            <div class="filter-set-content-head">
                                                <a href="#" class="collapsed" data-bs-toggle="collapse"
                                                    data-bs-target="#collapseOne" aria-expanded="false"
                                                    aria-controls="collapseOne">End Date</a>
                                            </div>
                                            <div class="filter-set-contents accordion-collapse collapse" id="collapseOne"
                                                data-bs-parent="#accordionExample">
                                                <div class="filter-content-list">
                                                    <ul>
                                                        <li>
                                                            <div class="filter-checks">
                                                                <label class="checkboxs">
                                                                    <input type="checkbox" checked />
                                                                    <span class="checkmarks"></span>
                                                                    19 Oct 2023
                                                                </label>
                                                            </div>
                                                        </li>
                                                        <li>
                                                            <div class="filter-checks">
                                                                <label class="checkboxs">
                                                                    <input type="checkbox" />
                                                                    <span class="checkmarks"></span>
                                                                    23 Nov 2023
                                                                </label>
                                                            </div>
                                                        </li>
                                                    </ul>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="filter-set-content">
                                            <div class="filter-set-content-head">
                                                <a href="#" class="collapsed" data-bs-toggle="collapse"
                                                    data-bs-target="#collapseThree" aria-expanded="false"
                                                    aria-controls="collapseThree">Status</a>
                                            </div>
                                            <div class="filter-set-contents accordion-collapse collapse"
                                                id="collapseThree" data-bs-parent="#accordionExample">
                                                <div class="filter-content-list">
                                                    <ul>
                                                        <li>
                                                            <div class="filter-checks">
                                                                <label class="checkboxs">
                                                                    <input type="checkbox" checked />
                                                                    <span class="checkmarks"></span>
                                                                    Success
                                                                </label>
                                                            </div>
                                                        </li>
                                                        <li>
                                                            <div class="filter-checks">
                                                                <label class="checkboxs">
                                                                    <input type="checkbox" />
                                                                    <span class="checkmarks"></span>
                                                                    Pending
                                                                </label>
                                                            </div>
                                                        </li>
                                                        <li>
                                                            <div class="filter-checks">
                                                                <label class="checkboxs">
                                                                    <input type="checkbox" />
                                                                    <span class="checkmarks"></span>
                                                                    Bounced
                                                                </label>
                                                            </div>
                                                        </li>
                                                        <li>
                                                            <div class="filter-checks">
                                                                <label class="checkboxs">
                                                                    <input type="checkbox" />
                                                                    <span class="checkmarks"></span>
                                                                    Paused
                                                                </label>
                                                            </div>
                                                        </li>
                                                    </ul>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="filter-reset-btns">
                                        <div class="row">
                                            <div class="col-6">
                                                <a href="#" class="btn btn-light">Reset</a>
                                            </div>
                                            <div class="col-6">
                                                <a href="campaign-archieve.html" class="btn btn-primary">Filter</a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="table-responsive custom-table">
                    <table class="table" id="campaign-list">
                        <thead class="thead-light">
                            <tr>
                                <th class="no-sort">
                                    <label class="checkboxs"><input type="checkbox" id="select-all" /><span
                                            class="checkmarks"></span></label>
                                </th>
                                <th></th>
                                <th>Name</th>
                                <th>Type</th>
                                <th>Progress</th>
                                <th>Members</th>
                                <th>Start Date</th>
                                <th>End Date</th>
                                <th>Status</th>
                                <th>Created</th>
                                <th class="text-end">Action</th>
                            </tr>
                        </thead>

                        <tbody></tbody>
                    </table>
                </div>
                <div class="row align-items-center">
                    <div class="col-md-6">
                        <div class="datatable-length"></div>
                    </div>
                    <div class="col-md-6">
                        <div class="datatable-paginate"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>


@endsection
