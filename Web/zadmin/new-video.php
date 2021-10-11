<?php include "initializer.php"; ?>

<!DOCTYPE html>
<html>

<head>
	<meta http-equiv="Content-Type" content="text/html; charset=gb18030">

	<?php include "meta.php"; ?>

	<title>Página de Inicio - <?php getString("main_title") ?></title>

	<meta name="description" content="Descripción de inicio - <?php getString("main_description") ?>">

	<?php include "head-css.php"; ?>

	<?php include "head-js.php"; ?>

	<style type="text/css">
		body.gray-body {
			background: #f1f1f1;
		}

		.small-card {
			height: 160px;
			background: #fff;
			border-radius: 10px;
			box-shadow: 0px 3px 8px rgba(0, 0, 0, 0.1);
			overflow: hidden;
			margin-bottom: 15px;
		}

		.ftd-img {
			height: 100%;
			background: #cacaca;
			background-image: url(https://i0.wp.com/zblogged.com/wp-content/uploads/2019/02/FakeDP.jpeg?resize=567%2C580&ssl=1);
			background-size: cover;
			background-position: center;
		}

		.small-card .title {
			font-size: 23px;
			margin: 10px 0px;
			margin-top: 15px;
		}

		.small-card .subtitle {
			display: block;
		}
	</style>
</head>

<body class="gray-body general">

	<?php include "credits.php"; ?>

	<?php include "navbar.php"; ?>

	<!-- Contenido principal -->

	<div class="main">

		<?php
		$dataArray = array(
			"bg_img" => "https://cdn.mos.cms.futurecdn.net/6vntE9WsMpsnCMou2Bp5KF.jpg",
			"title" => "Usuarios",
			"overlayOpacity" => "0.5",
			"overlayColor" => "linear-gradient(45deg, #ff66be, #fdca64)"
		);
		echo '
		<!-- Inicio Navbar -->
		' . $extras_class->getTemplate("header", $dataArray) . '
		<!-- Fin Navbar -->
		';
		?>
		<div class="dashboard col-md-12">

			<div class="tab" tab-name="users">
            <script type="text/javascript" src="{{ asset('jquery.fileupload/js/vendor/jquery.ui.widget.js') }}"></script>
<script type="text/javascript" src="{{ asset('jquery.fileupload/js/jquery.iframe-transport.js') }}"></script>
<script type="text/javascript" src="{{ asset('jquery.fileupload/js/jquery.fileupload.js') }}"></script>
<script src="{{ asset('bundles/fosjsrouting/js/router.js') }}"></script>
<script src="{{ path('fos_js_routing_js', {"callback": "fos.Router.setData"}) }}"></script>

<script type="text/javascript">
$(document).on('change', '.btn-file :file', function() {
  var input = $(this),
  numFiles = input.get(0).files ? input.get(0).files.length : 1,
  label = input.val().replace(/\\/g, '/').replace(/.*\//, '');
  input.trigger('fileselect', [numFiles, label]);
});
$(document).ready(function() {
    // Vimeo upload
    var uploaderStatus = $('#status-text');
    var uploadForm = $('#upload-form');
    var ajaxLoader = $('#ajax-loader');
    var checkProgressInterval = null;
    var checkProgressDelay = 1000;
    var ticket_id = null;
    var upload_link = null;
    var upload_link_secure = null;
    var complete_uri = null;
    var uri = null;
    var total_bytes = 0;
    var current_bytes = 0;
    var progress = 0;
    var maxFileSize = 500000000; // 500MB
    var fileTypeAllowed = /video\/(.*)/i;
    // Authenticate request
    $.ajax({
        type: "POST",
        url: Routing.generate('site_videofile_php_vimeo_request'), // PhpVimeo.request()
        data: {
            url: "/me/videos",
            params: {
                type: "streaming"
            },
            method: "POST"
        }
    })
    .done(function (data) {
        // Request authenticated
        data = $.parseJSON(data);
        data = data.response.body;
        ticket_id = data.ticket_id;
        upload_link = data.upload_link;
        upload_link_secure = data.upload_link_secure;
        complete_uri = data.complete_uri;
        uri = data.uri;
        uploadForm.attr('action', data.upload_link);
        uploadForm.fadeIn();
        // Init uploader
        uploaderStatus.html(Translator.trans('videoFile.uploader.status.ready'));
        ajaxLoader.css('display', 'none');
        toggleFileSelector('on');
        $('#file').fileupload({
            url: upload_link_secure,
            type: 'PUT',
            multipart: false,
            maxNumberOfFiles: 1,
            maxFileSize: maxFileSize,
            acceptFileTypes: /video\/.*/i,
            autoUpload: false
        }).bind('fileuploadadd', function (e, data) {
            uploaderStatus.html(Translator.trans('videoFile.uploader.status.checking'));
            ajaxLoader.css('display', 'inline');
            var file = data.files[0];
            // Check file size
            if (file.size > maxFileSize) {
                var maxFileSizeStr = parseFloat(Math.round(maxFileSize/1000000 * 100) / 100).toFixed(2) + 'MB';
                var fileSizeStr = parseFloat(Math.round(file.size/1000000 * 100) / 100).toFixed(2) + 'MB';
                createAlert(Translator.trans('videoFile.uploader.notices.fileSizeError', {'maxFileSize': maxFileSizeStr, 'fileSize': fileSizeStr}));
                uploaderStatus.html(Translator.trans('videoFile.uploader.status.ready'));
                ajaxLoader.css('display', 'none');
            }
            // Check file type
            else if (!file.type.match(fileTypeAllowed)) {
                createAlert(Translator.trans('videoFile.uploader.notices.fileTypeError'));
                uploaderStatus.html(Translator.trans('videoFile.uploader.status.ready'));
                ajaxLoader.css('display', 'none');
            }
            else {
                data.submit();
            }
        }).bind('fileuploadsubmit', function (e, data) {
            uploaderStatus.html(Translator.trans('videoFile.uploader.status.sending'));
            ajaxLoader.css('display', 'inline');
            // Get file size
            var file = data.files[0];
            total_bytes = data.files[0].size;
        }).bind('fileuploadsend', function (e, data) {
            data.headers = {}; // Prevent jQuery fileupload from sending Content-Disposition header 
                               // which is blocked by Vimeo
            createAlert(Translator.trans('videoFile.uploader.notices.started'), 'success');
        }).bind('fileuploadprogress', function () {
            // Get upload progress
            checkProgress(true);
            updateProgressBar();
        }).bind('fileuploaddone', function () {
            uploaderStatus.html(Translator.trans('videoFile.uploader.status.completing'));
            ajaxLoader.css('display', 'inline');
            // Complete upload
            checkProgress(false);
            if (current_bytes >= total_bytes) {
                $.ajax({
                    type: "POST",
                    url: Routing.generate('site_videofile_php_vimeo_request'), // PhpVimeo.request()
                    data: {
                        url: complete_uri,
                        method: "DELETE"
                    }
                }).done(function (data) {
                    data = $.parseJSON(data);
                    data = data.response;
                    if (data.status == 201) {
                        var location = data.headers.Location;
                        var vimeoId = location.replace(/\/videos\//, '');
                        progress = 100;
                        updateProgressBar();
                        createAlert(Translator.trans('videoFile.uploader.notices.success'), 'success');
                        uploaderStatus.html(Translator.trans('videoFile.uploader.status.processing'));
                        ajaxLoader.css('display', 'inline');
                        // Set Vimeo ID
                        $.ajax({
                            type: "GET",
                            url: Routing.generate('site_videofile_set_video_id', {'videoFile': '{{ videoFile.id }}', 'vimeoId': vimeoId}) // Stores video ID from Vimeo into local database
                        }).done(function () {
                            // Set metadata on Vimeo
                            $.ajax({
                                type: "POST",
                                url: Routing.generate('site_videofile_php_vimeo_request'), // PhpVimeo.request()
                                data: {
                                    url: location,
                                    params: {
                                        'name': "{{ videoFile.name }}",
                                        'description': "{{ videoFile.description }}",
                                        //'license': 'by',
                                        'privacy': {'view' : "disable"},
                                        //'privacy.embed': 'public',
                                        //'review_link': true
                                    },
                                    method: "PATCH"
                                }
                            }).done(function (data) {
                                createAlert(Translator.trans('videoFile.uploader.notices.processed'), 'success');
                                uploaderStatus.html(Translator.trans('videoFile.uploader.status.ready'));
                                ajaxLoader.css('display', 'none');
                                window.location.reload();
                            });
                        });
                    }
                    else {
                        createAlert(Translator.trans('videoFile.uploader.notices.error', {'status': data.status, 'message': data.body.error}), 'danger');
                        uploaderStatus.html(Translator.trans('videoFile.uploader.status.ready'));
                        ajaxLoader.css('display', 'none');
                    }
                });
            }
        });
    });
    function checkProgress(async) {
        var jqxhr = $.ajax({
            type: "PUT",
            url: upload_link_secure,
            async: async,
            headers: {
                "Content-Range": "bytes */*"
            }
        });
        jqxhr.always(function(data, textStatus, jqXHR) {
            var response = data.getResponseHeader('Range');
            current_bytes = response.replace(/[a-z0-9=]+-/, '');
            progress = (current_bytes/total_bytes) * 100;
        });
    }
    function createAlert(message, type) {
        if (typeof type == 'undefined') type = 'danger';
        var alert = '<div class="alert alert-' + type + '">';
        alert += message;
        alert += '<button type="button" class="close" data-dismiss="alert"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button></div>';
        $('#upload-alerts').append(alert);
    }
    function updateProgressBar() {
        $('#progress').find('.progress-bar').css('width', Math.round(progress) + '%');
    }
    function toggleFileSelector(state) {
        // Disable
        if (state == 'off') {
            $('.fileinput-button').addClass('disabled');
            $('#file').addAttr('disabled');
        }
        // Enable
        else {
            $('.fileinput-button').removeClass('disabled');
            $('#file').removeAttr('disabled');
        }
    }
});
</script>

<!-- Uploader -->
<div class="row">
    <div class="col-md-1">
        <span class="btn btn-primary disabled fileinput-button">
            <span style="font-size: 20px;" class="glyphicon glyphicon-folder-open"></span>
            <input type="file" name="file_data" id="file" class="form-control" disabled>
        </span>
    </div>
    <div class="col-md-8">
        <div class="progress" id="progress">
            <div class="progress-bar" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100" style="width: 0%;"></div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="well well-sm text-center" id="uploader-status">
            <span id="status-text">{{ 'videoFile.uploader.status.initializing'|trans }}</span>
            <span id="ajax-loader">&nbsp;&nbsp;
                <img src="{{ asset('img/ajax_loader.gif') }}" alt="loading" style="max-height: 16px;">
            </span>
        </div>
    </div>
</div>

<!-- Alerts -->
<div class="row">
    <div class="col-md-12" id="upload-alerts">
    </div>
</div>

			</div>

		</div>
	</div>

	<!-- Fin Contenido principal -->

	<?php
	echo '
	<!-- Inicio Navbar -->
	' . $extras_class->getTemplate("bottom-menu", $dataArray) . '
	<!-- Fin Navbar -->
	';
	?>

	<?php include "footer.php"; ?>

	<?php include "body-js.php"; ?>
</body>

</html>