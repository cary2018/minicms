/**
 *---------------------------------------------------------图片批量上传预览 START------------------------
 * @param fileid   当前属性id 直接 batch_img（this，5）
 * @param UpFileNumber  添加的数量
 */
function batch_img(fileid,UpFileNumber){
	let idfiles = $(fileid).attr("id");
	let inp = re_file(fileid);
	let inputName = $(fileid).siblings("img").attr('data-name');
	let nowtime = (new Date()).getTime();
	let fileReader = new FileReader();
	let fpat=document.getElementById(idfiles).files[0];
	fileReader.readAsDataURL(fpat);
	fileReader.onload=function (event){
		$(fileid).siblings("img").attr('src',this.result);
		$(fileid).siblings("img").attr('title',nowtime);
		$(fileid).attr('name',inputName);
	};
	let tl = $(fileid).siblings("img").attr('title');
	let numUp = $(fileid).parent().parent().children('section').length;
	let html_centent = $(fileid).parent('section').html();
	let id_img = new RegExp('title=\"(.*?)\"');
	let reg = new RegExp('src=\"(.*?)\"');
	let gal = new RegExp('id=\"gallery(.*?)\"');
	let imgdef = html_centent.replace(id_img,'title=""');
	let imgdefault = imgdef.replace(reg,'src="/images/all.png"');
	let html_show = imgdefault.replace(gal,'id="'+'gallery'+nowtime+'"');
	if(numUp < UpFileNumber){
		if(!tl && inp == 200){
			$(fileid).parent('section').after("<section class='z_file fl'>"+html_show+"</section>");
		}
	}
}
// 删除节点
function del_img(fileid,del_id='',path = ''){
	let fileids = $(fileid).siblings('input').attr("id");
	let imgUrl = $(fileid).siblings('img').attr("src");
	let inp = re_file(fileid);
	let default_img = "/template/conme/static/images/all.png";
	if(imgUrl != default_img){
		layer.confirm('你确定要删除吗？',function(data){
			//alert(del_id)
			if(del_id){
				$.get(path,{id:del_id},
					function(data){
						var mess = eval('('+data+')');
						if(mess['status']==200)
						{
							layer.msg(mess['msg'], {icon: 1,shade: [0.3, '#000'],time:1000});
						}
						if(mess['status']==300)
						{
							layer.msg(mess['msg'],{icon:2,shade: [0.3, '#000'],time:1000});
						}
					}
				);
			}
			//alert(inp)
			if(inp == 200){
				$(fileid).parent().remove();
				layer.close(data)
			}else{
				$(fileid).siblings('img').attr("src",default_img);
				$(fileid).siblings('img').attr("title",'');
				$('#'+fileids).val('');
				layer.close(data)
			}
		})
	}
}
// 计算数量
function re_file(fileid){
	let filearr = $(fileid).parent().parent('div').find('img');
	let number = $(fileid).parent().parent().children('section').length;
	for(i=0;i<number;i++ ){
		if(!filearr[i]['title']){
			var result = 200;
			break;
		}else{
			var result = 300;
		}
	}
	return result;
}
//---------------------------------------------------------图片批量上传预览 END------------------------