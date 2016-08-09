<script>
    document.addEventListener('DOMContentLoaded', function() {
        var hide = <?php echo json_encode($details['hide']) ?>;
        
        if(hide.indexOf(window.location.hash.substr(4)) != -1) {
            hide.forEach(function(x) {
                $('#tabs #menu' + x).hide();
            });
        } else {
            $('#menuMore i').removeClass('glyphicon glyphicon-plus').addClass('glyphicon glyphicon-minus');
        }
        
        $('#menuMore').click(function() {
            hide.forEach(function(x) {
                $('#tabs #menu' + x).toggle();
            });
            
            var icon = $('i', this);
            
            if(icon.hasClass('glyphicon glyphicon-plus'))
                icon.removeClass('glyphicon glyphicon-plus').addClass('glyphicon glyphicon-minus');
            else
                icon.removeClass('glyphicon glyphicon-minus').addClass('glyphicon glyphicon-plus');
        });
    });
</script>
