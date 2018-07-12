<?php if (!defined('__TYPECHO_ROOT_DIR__')) exit; ?><?phpdate_default_timezone_set('Asia/Shanghai');$id=substr($this->respondId, 13);$queryContents= $this->db->select()->from('table.contents')->where('cid = ?', $id); $rowContents = $this->db->fetchRow($queryContents);$action = isset($_POST['action']) ? addslashes($_POST['action']) : '';if($action=='comment'){	$text = isset($_POST['text']) ? addslashes($_POST['text']) : '';	$parent = isset($_POST['parent']) ? intval($_POST['parent']) : 0;	$comment=array(		'cid'=>$id,		'created'=>time(),		'author'=>$this->user->screenName,		'authorId'=>$this->user->uid,		'ownerId'=>$rowContents['authorId'],		'mail'=>$this->user->mail,		'url'=>$this->user->url,		'ip'=>$this->request->getIp(),		'agent'=>$_SERVER["HTTP_USER_AGENT"],		'text'=>$text,		'type'=>'comment',		'status'=>'approved',		'parent'=>$parent	);	Widget_Abstract_Comments::insert($comment);	$this->response->redirect($this->permalink);}?><div style="padding:10px;background-color:#fff;">  <div class="am-u-md-8 am-u-sm-centered">	<?php	if($this->allow('comment')){	?>	<div class="am-g">	  <div class="am-u-md-8 am-u-sm-centered">		<form class="am-form-inline" id="comment-form" role="form" method="post" action="">		  <div class="am-form-group">			<img class="am-radius" src="http://s.amazeui.org/media/i/demos/bw-2014-06-19.jpg?imageView/1/w/1000/h/1000/q/80" width="35" height="35"/>		  </div>		  <br />		  <div class="am-form-group">			<input type="text" name="text" id="comment-text" maxLength="140" class="am-form-field am-input-sm">		  </div>		  <br />		  <input type="hidden" name="action" value="comment">		  <input type="hidden" name="parent" value="0">		  <button type="submit" class="am-btn am-btn-warning am-radius am-btn-xs">评论</button>		  <input type="hidden" id="comment-login" data-login="<?=$this->user->hasLogin();?>"></p>		  <!-- Security -->		  <?php $security = $this->widget('Widget_Security'); ?>		  <input type="hidden" name="_" value="<?php echo $security->getToken($this->request->getReferer())?>"></p>		</form>	  </div>	</div>	<?php	}else{	?>		<button type="button" class="am-btn am-btn-default am-btn-block">评论关闭</button>	<?php	}	?>  </div></div><a name="comments"></a>
<?php $this->comments()->to($comments); ?>
<?php if ($comments->have()) : ?><ul class="am-comments-list" style="padding:10px;background-color:#fff;">	<?php	$queryTotal= $this->db->select('*,table.comments.created as ccreated,table.comments.text as ctext,table.comments.parent as cparent')->from('table.comments')->join('table.contents', 'table.comments.cid = table.contents.cid',Typecho_Db::INNER_JOIN)->where('table.comments.status = ?', 'approved')->where('table.comments.type = ?', 'comment')->where('table.comments.cid = ?', $id)->where('table.comments.parent = ?', 0);	$page_now = isset($_GET['page_now']) ? intval($_GET['page_now']) : 1;	if($page_now<1){		$page_now=1;	}	$resultTotal = $this->db->fetchAll($queryTotal);	$page_rec=$this->options->row['commentsListSize'];	$totalrec=count($resultTotal);	$page=ceil($totalrec/$page_rec);	if($page_now>$page){		$page_now=$page;	}	if($page_now<=1){		$before_page=1;		if($page>1){			$after_page=$page_now+1;		}else{			$after_page=1;		}	}else{		$before_page=$page_now-1;		if($page_now<$page){			$after_page=$page_now+1;		}else{			$after_page=$page;		}	}	$i=($page_now-1)*$page_rec<0?0:($page_now-1)*$page_rec;	$queryComments= $this->db->select('*,table.comments.created as ccreated,table.comments.text as ctext,table.comments.parent as cparent')->from('table.comments')->join('table.contents', 'table.comments.cid = table.contents.cid',Typecho_Db::INNER_JOIN)->where('table.comments.status = ?', 'approved')->where('table.comments.type = ?', 'comment')->where('table.comments.cid = ?', $id)->where('table.comments.parent = ?', 0)->order('ccreated',Typecho_Db::SORT_DESC)->offset($i)->limit($page_rec);	$resultComments = $this->db->fetchAll($queryComments);	$i=1;	?>    <?php foreach($resultComments as $value){ ?>		<li class="am-comment">			<a href="#link-to-user-home">				<?php				$host = 'https://secure.gravatar.com';				$url = '/avatar/';				$size = '50';				$rating = Helper::options()->commentsAvatarRating;				$hash = md5(strtolower($value['mail']));				$avatar = $host . $url . $hash . '?s=' . $size . '&r=' . $rating . '&d=';				?>				<img src="<?=$avatar;?>" alt="" class="am-comment-avatar" width="48" height="48">			</a>			<div class="am-comment-main">			  <header class="am-comment-hd">				<div class="am-comment-meta">				  <a href="#link-to-user" class="am-comment-author"><?php echo $value['author']; ?></a><time datetime="<?php echo date('Y-m-d H:i',$value['ccreated']); ?>" title="<?php echo date('Y-m-d H:i',$value['ccreated']); ?>"><?php echo date('Y-m-d H:i',$value['ccreated']); ?></time>评论				  <div class="am-fr">					<a href="javascript:;" class="replyfloor" id="replyfloor<?=$i;?>" data-coid="<?php echo $value['coid']; ?>" data-author="<?php echo $value['author']; ?>" data-ccreated="<?php echo date('Y-m-d H:i',$value['ccreated']); ?>" data-ctext="<?php echo htmlspecialchars(strip_tags($value['ctext'])); ?>">回复</a>					#<?php echo (($page_now-1)*$page_rec+$i);?>				  </div>				</div>			  </header>			  <div class="am-comment-bd">				<p><?php echo $value['ctext']; ?></p>				<?php				$querySubComment= $this->db->select()->from('table.comments')->where('parent = ?', $value['coid'])->order('created',Typecho_Db::SORT_DESC);				$rowSubComment = $this->db->fetchAll($querySubComment);				?>				<?php foreach($rowSubComment as $value){ ?>				<header class="am-comment-hd">										<div class="am-list-item-text">					  <a href="#link-to-user" class="am-comment-author"><?php echo $value['author']; ?></a><time datetime="<?php echo date('Y-m-d H:i',$value['created']); ?>" title="<?php echo date('Y-m-d H:i',$value['created']); ?>"><?php echo date('Y-m-d H:i',$value['created']); ?></time>评论<p><?php echo $value['text']; ?></p>					</div>									</header>				<?php }?>			  </div>			</div>		</li>		<?php		$i++;	}	?></ul><ul class="am-pagination blog-pagination">  <?php if($page_now!=1){?>	<li class="am-pagination-prev"><a href="?page_now=1">首页</a></li>  <?php }?>  <?php if($page_now>1){?>	<li class="am-pagination-prev"><a href="?page_now=<?=$before_page;?>">&laquo; 上一页</a></li>  <?php }?>  <?php if($page_now<$page){?>	<li class="am-pagination-next"><a href="?page_now=<?=$after_page;?>">下一页 &raquo;</a></li>  <?php }?>  <?php if($page_now!=$page){?>	<li class="am-pagination-next"><a href="?page_now=<?=$page;?>">尾页</a></li>  <?php }?></ul><div class="am-modal am-modal-prompt" tabindex="-1" id="replydialog"><form class="am-form" id="reply-form" method="post" action="">  <div class="am-modal-dialog">    <div class="am-modal-hd">评论</div>    <div class="am-modal-bd">	      <input type="text" name="text" id="reply-text" class="am-modal-prompt-input">	  <!-- Security -->	  <?php $security = $this->widget('Widget_Security'); ?>	  <input type="hidden" name="_" value="<?php echo $security->getToken($this->request->getReferer())?>"></p>    	</div>    <div class="am-modal-footer">      <span class="am-modal-btn">		<button type="button" class="am-btn am-btn-warning am-radius am-btn-sm" data-am-modal-cancel>取消</button>	  </span>      <span class="am-modal-btn">		<input type="hidden" name="action" value="comment">		<input type="hidden" id="parent" name="parent" value="0">		<button type="submit" class="am-btn am-btn-warning am-radius am-btn-sm" data-am-modal-confirm>提交</button>	  </span>    </div>  </div></form></div>
<?php endif; ?>
<!-- end post comment --><script>$(function() {	$('#comment-form').submit(function(){		if($('#comment-text').val()==''){			return false;		}		if($('#comment-login').attr('data-login')!=1){			$('#login-prompt').modal();			return false;		}	});	$('#reply-form').submit(function(){		if($('#reply-text').val()==''){			return false;		}	});	$(".replyfloor").each(function(){		var id=$(this).attr("id");		$("#"+id).click( function () {			if($('#comment-login').attr('data-login')!=1){				$('#login-prompt').modal();				return;			}			$('#parent').val($(this).attr('data-coid'));			$('#replydialog').modal({			  relatedTarget: this,			  onConfirm: function(e) {				$('#reply-form').submit();			  },			  onCancel: function(e) {			  }			});		});	});});</script>