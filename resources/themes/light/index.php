<!DOCTYPE html>
<html>
    <head>
        <title>Directory listing of <?php echo $index->getListedPath(); ?></title>
        <link rel="shortcut icon" href="<?php echo THEMEPATH; ?>/img/folder.png">
        <!-- STYLES -->
        <link rel="stylesheet" type="text/css" href="<?php echo THEMEPATH; ?>/css/bootstrap.min.css">
        <link rel="stylesheet" type="text/css" href="<?php echo THEMEPATH; ?>/css/font-awesome.min.css">
        <link rel="stylesheet" type="text/css" href="<?php echo THEMEPATH; ?>/css/style.css">
        <!-- SCRIPTS -->
        <script type="text/javascript" src="//ajax.googleapis.com/ajax/libs/jquery/2.0.3/jquery.min.js"></script>
        <script type="text/javascript" src="<?php echo THEMEPATH; ?>/js/bootstrap.min.js"></script>
        <script type="text/javascript" src="<?php echo THEMEPATH; ?>/js/directorylister.js"></script>
        <!-- FONTS -->
        <link rel="stylesheet" type="text/css"  href="//fonts.googleapis.com/css?family=Cutive+Mono">
        <!-- META -->
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta charset="utf-8">
    </head>
    <body>
        <div id="page-navbar" class="navbar navbar-inverse navbar-fixed-top">
            <div class="container">

                <?php $breadcrumbs = $index->listBreadcrumbs(); ?>

                <p class="navbar-text">
                    <?php foreach($breadcrumbs as $breadcrumb): ?>
                        <?php if ($breadcrumb != end($breadcrumbs)): ?>
                                <a href="<?php echo $breadcrumb['link']; ?>"><?php echo $breadcrumb['text']; ?></a>
                                <span class="divider">/</span>
                        <?php else: ?>
                            <?php echo $breadcrumb['text']; ?>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </p>

                <div class="navbar-right">
                    <ul id="page-top-nav" class="nav navbar-nav">
                        <li><a href="javascript:void(0)" id="page-top-link"><i class="fa fa-arrow-circle-up fa-lg"></i></a></li>
                    </ul>
                </div>

            </div>
        </div>

        <div id="page-content" class="container">

            <?php if($index->getSystemMessages()): ?>
                <?php foreach ($index->getSystemMessages() as $message): ?>
                    <div class="alert alert-<?php echo $message['type']; ?>">
                        <?php echo $message['text']; ?>
                        <a class="close" data-dismiss="alert" href="#">&times;</a>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
            
            <div class="tab-container">
                <div id="directory-list-header">
                    <div class="row">
                        <div class="col-md-7 col-sm-6 col-xs-10 text-left">File</div>
                        <div class="col-md-2 col-sm-2 col-xs-2 text-right">Size</div>
                        <div class="col-md-3 col-sm-4 hidden-xs text-right">Last Modified</div>
                    </div>
                </div>

                <ul id="directory-listing" class="nav nav-pills nav-stacked">

                    <?php foreach($dirArray as $name => $fileInfo): ?>
                        <li data-name="<?php echo $name; ?>" data-href="<?php echo $fileInfo['url_path']; ?>">
                            <a href="<?php echo $fileInfo['url_path']; ?>" class="clearfix tr" data-name="<?php echo $name; ?>">

                                <div class="row">
                                    <span class="file-name col-md-7 col-sm-6 col-xs-9 text-left">
                                        <i class="fa <?php echo $fileInfo['icon_class']; ?> fa-fw"></i>
                                        <?php echo $name; ?>
                                    </span>

                                    <span class="file-size col-md-2 col-sm-2 col-xs-3 text-right">
                                        <?php echo $fileInfo['file_size']; ?>
                                    </span>

                                    <span class="file-modified col-md-3 col-sm-4 hidden-xs text-right">
                                        <?php echo $fileInfo['mod_time']; ?>
                                    </span>
                                </div>

                            </a>

                            <?php if (is_file($fileInfo['file_path'])): ?>
                                <a href="javascript:void(0)" class="file-download-button">
                                    <i class="fa fa-download"></i>
                                </a>
                            <?php endif; ?>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
            <hr>
            <div class="footer">
                <?php /* Powered by, <a href="http://www.directorylister.com">Directory Lister</a> */ ?>
            </div>
        </div>
    </body>
</html>
