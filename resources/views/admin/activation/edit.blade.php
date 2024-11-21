@extends('admin.layouts.master')
@section('title', 'Kart Düzenle')
@section('content')
    <div class="row">
        <div class="col-xl-12 col-xxl-12 col-lg-12 mx-auto">
            <div class="row">
                <div class="col-xl-12">
                    <div class="card profile-card">
                        <div class="card-header flex-wrap border-0 pb-0">
                            <h3 class="fs-24 text-black font-w600 me-auto mb-2 pe-3">Ürün Düzenle</h3>
                        </div>
                        <div class="card-body">
                            <form>
                                <div class="row">
                                    <div class="col-xl-6 col-sm-6">
                                        <div class="form-group">
                                            <label>Ürün Adı</label>
                                            <input type="text" class="form-control"
                                                   value="White Business Card PVC - Dijital kartvizit">
                                        </div>
                                    </div>
                                    <div class="col-xl-6 col-sm-6">
                                        <div class="form-group">
                                            <label>Fiyat</label>
                                            <input type="text" class="form-control" value="369">
                                        </div>
                                    </div>
                                    <div class="col-xl-3 col-sm-6">
                                        <label>İndirim Oranı</label>
                                        <select class="form-select">
                                            <option value="yok">İndirim Yok</option>
                                            <option value="percent">%</option>
                                            <option value="currency" selected>TL</option>
                                        </select>
                                    </div>
                                    <div class="col-xl-3 col-sm-6">
                                        <div class="form-group">
                                            <label>İndirim Tutarı <span style="color: #f00; font-size: 12px;"> *İndirim Yok İseBoş Bırakınız</span></label>
                                            <input type="text" class="form-control" value="81">
                                        </div>
                                    </div>
                                    <div class="col-xl-3 col-sm-6">
                                        <label>Renk Seçenekleri</label>
                                        <select class="form-select">
                                            <option value="yok" selected>Renk Seçeneği Yok</option>
                                            <option value="Kırmızı">Kırmızı</option>
                                            <option value="Mavi">Mavi</option>
                                            <option value="Siyah">Siyah</option>
                                            <option value="Yeşil">Yeşil</option>
                                            <option value="Gri">Gri</option>
                                            <option value="Pembe">Pembe</option>
                                            <option value="Sarı">Sarı</option>
                                        </select>
                                    </div>
                                    <div class="col-xl-3 col-sm-6">
                                        <div class="form-group">
                                            <label>Stok Adedi</label>
                                            <input type="text" class="form-control" value="999">
                                        </div>
                                    </div>
                                    <div class="col-xl-12 col-sm-6">
                                        <div class="form-group">
                                            <label>Ürün Resmi <span style="color: #f00; font-size: 12px;">*Çoklu Yükleme Yapılabilir</span></label>
                                            <input type="file" class="form-control" accept="image/png, image/gif, image/jpeg" multiple>
                                        </div>
                                    </div>
                                </div>
                                <div class="row mb-4">
                                    <div class="col-xl-3">
                                        <img src="images/product/p1-1.jpg" class="img-responsive" width="100%" alt="">
                                        <button type="button" class="btn btn-danger btn-sm w-100 my-2 delete_btn">Resimi Sil</button>
                                    </div>
                                    <div class="col-xl-3">
                                        <img src="images/product/p1-2.jpg" class="img-responsive" width="100%" alt="">
                                        <button type="button" class="btn btn-danger btn-sm  w-100 my-2 delete_btn">Resimi Sil</button>
                                    </div>
                                    <div class="col-xl-3">
                                        <img src="images/product/p1-3.jpg" class="img-responsive" width="100%" alt="">
                                        <button type="button" class="btn btn-danger btn-sm w-100 my-2 delete_btn">Resimi Sil</button>
                                    </div>
                                    <div class="col-xl-3">
                                        <img src="images/product/p1-4.jpg" class="img-responsive" width="100%" alt="">
                                        <button type="button" class="btn btn-danger btn-sm w-100 my-2 delete_btn">Resimi Sil</button>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-xl-12">
                                        <div class="form-group">
                                            <label>Açıklama</label>
                                            <textarea class="form-control" rows="6">
                                                Lorem ipsum dolor sit amet, consectetur adipiscing elit,
                                                sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.
                                                Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.
                                                Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur.
                                                Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum que laudantium,
                                                totam rem aperiam, eaque ipsa quae ab illo inventore veritatis et quasi architecto beatae vitae dicta su
                                            </textarea>
                                        </div>
                                    </div>
                                </div>
                                <div class="row" id="DetailsList">
                                    <div class="col-md-10 mb-3">
                                        <label for="">Ek Bilgi</label>
                                        <input type="text" class="form-control" name="details[]" maxlength="200" minlength="3" value="Harika bir Kart özelliği!">
                                    </div>
                                    <div class="col-md-1 mb-3">
                                        <label for=""></label>
                                        <button type="button" class="btn btn-primary school1" style="display: block;" onclick="addDetails()">Bilgi Ekle</button>
                                    </div>
                                </div>
                                <div class="d-sm-flex d-block justify-content-end">
                                    <a class="btn btn-primary btn-rounded mb-2" href="#">Ürün Oluştur</a>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection