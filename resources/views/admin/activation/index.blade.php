@extends('admin.layouts.master')
@section('title', 'Aktivasyon')
@section('content')
<link rel="stylesheet" href="{{ asset('default/css/network.css') }}">
    <div class="page-titles d-flex justify-content-between">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="javascript:void(0)">Aktivasyon</a></li>
            <li class="breadcrumb-item active"><a href="javascript:void(0)">Aktivasyon Listesi</a>
            </li>
        </ol>
    </div>
    <!-- row -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <div class="col-md-6">
                        <h4 class="card-title">Aktivasyon Kod Listesi</h4>
                    </div>
                    <div class="col-md-6">
                        <!-- Arama Formu -->
                        <form method="GET" action="{{ route('admin.activationCode.search') }}">
                            <div class="form-group">
                                <input type="text" name="search" value="{{ request('search') }}" class="form-control" placeholder="Aktivasyo kodu ile arayın">
                            </div>
                            <button type="submit" class="btn btn-primary">Ara</button>
                        </form>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-responsive-md">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>QR</th>
                                    <th>Kod</th>
                                    <th>Kullanıcı</th>
                                    <th>Durum</th>
                                    <th>Kategori</th>
                                    <th>Oluşturulma Tarihi</th>
                                    <th>İşlem</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($activations as $activation)
                                    <tr>
                                        <td>{{ $activation->id }}</td>
                                        <td>
                                            <div class="qr-code-viewer">
                                                <div class="qr-code-viewer-inner">
                                                    <div class="qr-code-side">
                                                        <div name="QrArea" id="QrArea"></div>
                                                    </div>
                                                    <div class="qr-code-logo" id="personal">
                                                    <img @if ($activation->user and $activation->user->userQr) src="{{ asset($activation->user->userQr->logo) }}" @endif
                                                    alt="">
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                        <td>{{ $activation->activation_code }}</td>
                                        <td>@if ($activation->user) {{ $activation->user->email }} @endif</td>
                                        <td>{{ $activation->used_text }}</td>
                                        <td>{{ $activation->category->name }}</td>
                                        <td>{{ $activation->created_at }}</td>
                                        <td>
                                            <div class="d-flex">
                                                @if ($activation->user and $activation->user->userQr)
                                                <a href="javascript:void(0)" data-id="{{ $activation->id }}" data-activation-code="{{ $activation->activation_code }}" data-activation-code-url="https://diji-card.com/{{ $activation->activation_code }}" data-color="{{ $activation->user->userQr->color }}" data-bg-color="{{ $activation->user->userQr->bg_color }}"
                                                    class="btn btn-danger shadow btn-xs sharp downloadQr">
                                                    <i class="fa fa-download"></i>
                                                </a>&nbsp;
                                                @else
                                                <a href="javascript:void(0)" data-id="{{ $activation->id }}" data-activation-code="{{ $activation->activation_code }}" data-activation-code-url="https://diji-card.com/{{ $activation->activation_code }}" data-color="" data-bg-color=""
                                                    class="btn btn-danger shadow btn-xs sharp downloadQr">
                                                    <i class="fa fa-download"></i>
                                                </a>&nbsp;
                                                @endif
                                                <a href="{{ route('admin.activationCode.destroy',$activation->id) }}"
                                                    class="btn btn-danger delete_btn shadow btn-xs sharp">
                                                    <i class="fa fa-trash"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>

                        {{ $activations->appends(request()->input())->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
<script src="https://cdn.jsdelivr.net/npm/qr-creator/dist/qr-creator.min.js"></script>

<script>
const QR = (() => {
    var downloadBtn;
    const getId = () => { return downloadBtn.data('id'); };
    const getUrl = () => { return downloadBtn.data('activation-code-url'); };
    const getCode = () => { return downloadBtn.data('activation-code'); };
    const getQrArea = () => { return downloadBtn.closest('tr').find('[name="QrArea"]'); };
    const getQrLogo = () => { return downloadBtn.closest('tr').find('.qr-code-logo'); };
    const getColor = () => { return downloadBtn.data('color'); };
    const getBgColor = () => { return downloadBtn.data('bg-color'); };

    function combineCanvasAndImage() {
        var qrCanvas = getQrArea().find('canvas');
        var logoImg = getQrLogo().find("img");

        // Yeni bir canvas oluşturalım
        var finalCanvas = document.createElement('canvas');
        finalCanvas.width = qrCanvas.width();
        finalCanvas.height = qrCanvas.height();
        var ctx = finalCanvas.getContext('2d');

        // QR kodunu yeni canvas'a kopyalayalım
        ctx.drawImage(qrCanvas[0], 0, 0);

        // Logoyu yeni canvas'ın ortasına ekleyelim
        var logoX = (qrCanvas.width() - logoImg.width()) / 2;
        var logoY = (qrCanvas.height() - logoImg.height()) / 2;

        // Yuvarlak bir clipping path oluşturalım
        ctx.beginPath();
        ctx.arc(logoX + logoImg.width() / 2, logoY + logoImg.height() / 2, logoImg.width() / 2, 0, Math.PI * 2);
        ctx.clip();

        // Logoyu bu clipping path içerisine çizdirelim
        ctx.drawImage(logoImg[0], logoX, logoY, logoImg.width(), logoImg.height());

        return finalCanvas;
    }

    const download = (format) => {
        var finalCanvas = combineCanvasAndImage();
        var downloadLink = document.createElement('a');
        document.body.appendChild(downloadLink);

        if (format === 'png') {
            downloadLink.href = finalCanvas.toDataURL('image/png');
            downloadLink.download = 'qr-code-' + getCode() + '.png';
        } else if (format === 'jpg') {
            downloadLink.href = finalCanvas.toDataURL('image/jpeg');
            downloadLink.download = 'qr-code-' + getCode() + '.jpg';
        } else if (format === 'svg') {
            // SVG formatı için ek bir işlem gerekmektedir. Bu özellik, bu basit örnekte desteklenmemektedir.
            alert("SVG formatı şu anda desteklenmemektedir.");
            return;
        } else if (format === 'pdf') {
            // PDF formatı için ek bir kütüphaneye (örn. jsPDF) ihtiyaç duyulmaktadır.
            alert("PDF formatı şu anda desteklenmemektedir.");
            return;
        }

        downloadLink.click();
        document.body.removeChild(downloadLink);
    }

    const renderQR = () => {
        let fillValue = getColor();
        let bgValue = getBgColor();
        let personalText = getUrl();

        if (fillValue !== undefined) {
            fill = fillValue;
        }
        if (bgValue !== undefined) {
            background = bgValue;
        }

        let $qrArea = getQrArea();

        $qrArea.html('');

        QrCreator.render({
            text: personalText,
            radius: 0.5,
            ecLevel: 'H',
            fill: fill,
            background: background,
            image: 'https://www.dijicard.com/images/logo.png',
            size: 128
        }, $qrArea[0]);
    }

    // Public initialization function
    const init = () => {
        $('.downloadQr').on('click', function() {
            console.log("clicked");

            downloadBtn = $(this);

            // render the qr code
            renderQR();

            // download the qr code
            download('png');
        })
    };

    // Expose public functions
    return { init };
})();

document.addEventListener("DOMContentLoaded", () => {
    QR.init();
});
</script>