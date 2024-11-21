@extends('admin.layouts.master')
@section('title', 'Aktivasyon Oluştur')
@section('content')
    <div class="row">
        <div class="col-xl-12 col-xxl-12 col-lg-12 mx-auto">
            <div class="row">
                <div class="col-xl-12">
                    <div class="card profile-card">
                        <div class="card-header flex-wrap border-0 pb-0">
                            <h3 class="fs-24 text-black font-w600 me-auto mb-2 pe-3">Aktivasyon Oluştur</h3>
                        </div>
                        <div class="card-body">
                            <form action="{{ route('admin.activationCode.store') }}" method="post">
                                @csrf
                                <div class="row">
                                    <div class="col-xl-3 col-sm-6">
                                        <label class="form-label" for="type">Kategori</label>
                                        <select name="type" class="form-control form-select">
                                            <option value="">seçiniz</option>
                                            @foreach ($categories as $category)
                                                <option @if ($category->id == 2) selected @endif value="{{ $category->id }}">{{ $category->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-xl-3 col-sm-6">
                                        <label class="form-label" for="count">Aktivasyon Kod Adet</label>
                                        <input type="number" class="form-control" name="count" placeholder="Aktivasyon Kod Adet">
                                    </div>
                                </div>
                                <div class="d-sm-flex d-block justify-content-end">
                                    <button type="submit" class="btn btn-primary btn-rounded mb-2" href="#">Aktivasyon Kod Oluştur</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
