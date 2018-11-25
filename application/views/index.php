<!DOCTYPE html>

<html>
<head>
    <title>Farmgame</title>

    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="<?php echo base_url('assets/css/'); ?>bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <header>
        <div class="collapse bg-dark" id="navbarHeader">
            <div class="container">
                <div class="row">
                    <div class="col-sm-8 col-md-7 py-4">
                        <h4 class="text-white">About</h4>
                        <p class="text-muted">This is Farm Game developed for the assignment by Bleihm Chalcot.</p>
                    </div>
                    <div class="col-sm-4 offset-md-1 py-4">
                        <h4 class="text-white">Contact</h4>
                        <ul class="list-unstyled">
                            <li><a href="#" class="text-white">shivendrsingh.it@gmail.com</a></li>
                            <li><a href="#" class="text-white">8412904065</a></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
        <div class="navbar navbar-dark bg-dark shadow-sm">
            <div class="container d-flex justify-content-between">
                <a href="javascript:void(0);" class="navbar-brand d-flex align-items-center">
                    <i class="fa fa-game"></i>
                    <strong>Farm Game</strong>
                </a>
                <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarHeader" aria-controls="navbarHeader" aria-expanded="false" aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon"></span>
                </button>
            </div>
        </div>
    </header>
    <main role="main">
        <section class="jumbotron text-center">
            <div class="container">
                <h1 class="jumbotron-heading">Farm Game</h1>
                <p class="lead text-muted">You have a farm with:</p>
                <ol>
                    <li>farmer: needs to be fed at least once every 15 turns or else he/she will die.</li>
                    <li>cows: each cow needs to be fed at least once every 10 turns or else it will die.</li>
                    <li>bunnies: each bunny needs to be fed at least once every 8 turns or else it will die.</li>
                </ol>
                <p>If any of the animals or the farmer are not fed on time, they die. If the farmer dies, all animals die and the game is over.The game ends after 50 turns. If the farmer and at least one cow and one bunny are still alive at that point, you win.</p>               
                <div class="row">
                    <div class="col-md-12">
                        <table class="table">
                            <thead class="thead-dark">
                                <tr>
                                    <th scope="col">#</th>
                                    <?php 
                                    foreach ($component as $c => $component_data) {
                                        for($i = 0; $i < $component_data['count']; $i++){ ?>
                                            <th scope="col"><?php echo $c . ' ' . ($i+1); ?></th>
                                        <?php }
                                    }
                                    ?>
                                </tr>
                            </thead>
                            <tbody id="tbody-print">
                            </tbody>
                        </table>
                    </div>
                </div>
                <p>
                    <button class="btn btn-primary my-2" id="round">Play Round</button>
                    <button class="btn btn-primary my-2" id="restart-round">Start Game</button>
                </p>
            </div>
        </section>
    </main>

    <script src="<?php echo base_url('assets/js/'); ?>jquery-3.3.1.min.js" type="text/javascript"></script>
    <script src="<?php echo base_url('assets/js/'); ?>bootstrap.min.js" type="text/javascript"></script>

    <script type="text/template" id="row-template">
        <tr id="round-{0}">
            <td>{0}</td>
            <?php 
            foreach ($component as $c => $component_data) {
                for($i = 0; $i < $component_data['count']; $i++){ ?>
                    <td id="round-{0}-<?php echo $c . '-' . ($i+1); ?>"></td>
                <?php }
            }
            ?>
        </tr>
    </script>

    <script type="text/javascript">
        $(document).ready(function() {
            $('#round').hide();
            $('#restart-round').show();
            $('#round').click(function(e) {
                e.preventDefault();

                var site_url = '<?php echo base_url();?>';
                $.ajax({
                    type: "POST",
                    dataType : "json",
                    data: {"click" : 1},
                    url: site_url + 'play-round',
                    success: function(resp) 
                    {
                        var data = $("#row-template").html();
                        data = data.format(resp.round);
                        $("#tbody-print").append(data);

                        var fed_element_id = "round-" + resp.round + "-" + resp.component_fed_name + "-" + (resp.component_fed_index + 1);
                        $("#" + fed_element_id).html("Fed");
                        $("#" + fed_element_id).addClass("bg-success");

                        for(var i = 0; i < resp.component_died.length; i++){
                            var died_element_id = "round-" + resp.round + "-" + resp.component_died[i]['name'] + "-" + (resp.component_died[i]['index']);

                            $("#" + died_element_id).html("Died");
                            $("#" + died_element_id).addClass("bg-danger");                         
                        }
                        if(resp.game_result != 0){   
                            if(resp.game_result == 1){
                                $('#round').html("You Won!");
                            }
                            if(resp.game_result == 2){
                                $('#round').html("You Lost! " + resp.game_over_reason);
                            }
                            $('#round').attr("disabled", "disabled");
                        }
                    }
                });
            });

            $('#restart-round').click(function(e) {
                e.preventDefault();
                $(this).html('Restart Game');
                $(this).hide();
                $('#round').show();
                var site_url = '<?php echo base_url();?>';
                $.ajax({
                    type: "POST",
                    dataType : "json",
                    data: {"restart" : 1},
                    url: site_url + 'play-round',
                    success: function(resp) 
                    {
                        var data = $("#row-template").html();
                        data = data.format(resp.round);
                        $("#tbody-print").append(data);
                        var fed_element_id = "round-" + resp.round + "-" + resp.component_fed_name + "-" + (resp.component_fed_index + 1);

                        $("#" + fed_element_id).html("Fed");
                        $("#" + fed_element_id).addClass("bg-success");

                        for(var i = 0; i < resp.component_died.length; i++){
                            var died_element_id = "round-" + resp.round + "-" + resp.component_died[i]['name'] + "-" + (resp.component_died[i]['index']);

                            $("#" + died_element_id).html("Died");
                            $("#" + died_element_id).addClass("bg-danger");                         
                        }
                    }
                });
            });
        });

        if (!String.prototype.format) {
            String.prototype.format = function() {
                var args = arguments;
                return this.replace(/{(\d+)}/g, function(match, number) { 
                    return typeof args[number] != 'undefined'
                    ? args[number]
                    : match
                    ;
                });
            };
        }
    </script>
</body>
</html>