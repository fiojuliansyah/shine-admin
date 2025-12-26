$(function (){
                console.log($('#jobdesk'))

    $('.btn_edit').on('click', function(){

        const id = $(this).data('id')

        $.ajax({
            url: `/manage/task-planner/${id}/edit`,
            method: "GET",
            dataType: 'json',
            success: function(data){
                console.log($('#jobdesk'))
                 // Isi input
                $('.id_planner').val(data.id_planner); 
                $('.jobdesk').val(data.jobId); 
                $('.start_date').val(data.start_date);
                $('.time').val(data.time);
            }
        })
    })
})