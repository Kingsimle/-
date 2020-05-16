<?php
/*
Template Name: 标签云
*/
get_header();?>
<style>
	#tags{position:relative;height:340px;margin:10px auto 0;font-family: Play,"å¾®è½¯é›…é»‘","Microsoft YaHei UI","Microsoft YaHei","PingFang SC","Helvetica Neue",Helvetica,Arial,sans-serif!important;}
	#tags a{position:absolute;text-align:center;text-overflow:ellipsis;white-space:nowrap;top:0;left:0;padding:15px 20px;border:none;white-space:normal;font-size:30px}
	#tags a:hover{display:block;font-size:50px!important;padding:25px 30px;z-index:999;transition: all 0.5s}
	#tags a:nth-child(n){color:#20a0ff;border-radius:20px;display:inline-block;line-height:1.1em;margin:0 10px 15px 0}
	#tags a:nth-child(2n){color:#0041A5}
	#tags a:nth-child(3n){color:#ff8400}
	#tags a:nth-child(4n){color:#949C97}
	#tags a:nth-child(5n){color:#363532}
	#tags a:nth-child(6n){color:#A71368}
	#tags a:before {content: "";position: absolute;left: 0;top: 0;right: 0;bottom: 0;color: inherit;background: currentColor;border-radius: 20px;opacity: .15;}
</style>
<?php
$frontpage_carousels_type = _opt('frontpage_carousels_type');$type = strstr($frontpage_carousels_type, 'full') ? 'single-imageflow-full':'single-imageflow';get_topSlider(array($post->ID),$type);?><div class="container postListsModel"><div class="row"><?php
if (_opt('is_single_post_hide_sidebar')) {$leftClass = 'col-xs-12 no-sidebar';$rightClass = 'hidden';} else {$leftClass = 'col-md-9 col-lg-9_5';$rightClass = 'col-md-3 col-lg-2_5 hidden-xs hidden-sm';}?><div class="<?php echo $leftClass; ?>"><div class="col-xs-12">
<div class="row postLists"><div class="toggle_sidebar" @click="this.single_toggle_sidebar()" data-toggle="tooltip" data-placement="auto top" title="切换边栏"><i class="fas fa-angle-right"></i></div>
<div class="article_wrapper post clearfix page">
	<article class="clearfix">
		<div class="archives" id="tags">	
				<?php wp_tag_cloud('smallest=18&largest=18&unit=px&number=99');?>
		</div>
	</article>
</div>
<?php comments_template(); ?>
</div></div></div>

	<div class="<?php echo $rightClass; ?>">
		<div class="row">
			<div class="sidebar sidebar-affix">
				<div manual-template="sidebarMenu"></div>
				<div manual-template="sidebar"></div>
			</div>
		</div>
	</div>
</div>
</div>
<script>var radius=200;var dtr=Math.PI/180;var ddd=200;var mcList=[];var active=false;var lasta=1;var lastb=1;var distr=true;var tspeed=10;var size=200;var mouseX=0;var mouseY=0;var howElliptical=1;var aA=null;var oDiv=null;window.onload=function(){var i=0;var oTag=null;oDiv=document.getElementById('tags');aA=oDiv.getElementsByTagName('a');for(i=0;i<aA.length;i++){oTag={};oTag.offsetWidth=aA[i].offsetWidth;oTag.offsetHeight=aA[i].offsetHeight;mcList.push(oTag)}sineCosine(0,0,0);positionAll();oDiv.onmouseover=function(){active=true};oDiv.onmouseout=function(){active=false};oDiv.onmousemove=function(ev){var oEvent=window.event||ev;mouseX=oEvent.clientX-(oDiv.offsetLeft+oDiv.offsetWidth/2);mouseY=oEvent.clientY-(oDiv.offsetTop+oDiv.offsetHeight/2);mouseX/=5;mouseY/=5};setInterval(update,90)};function update(){var a;var b;if(active){a=(-Math.min(Math.max(-mouseY,-size),size)/radius)*tspeed;b=(Math.min(Math.max(-mouseX,-size),size)/radius)*tspeed}else{a=lasta*0.98;b=lastb*0.98}lasta=a;lastb=b;if(Math.abs(a)<=0.01&&Math.abs(b)<=0.01){return}var c=0;sineCosine(a,b,c);for(var j=0;j<mcList.length;j++){var rx1=mcList[j].cx;var ry1=mcList[j].cy*ca+mcList[j].cz*(-sa);var rz1=mcList[j].cy*sa+mcList[j].cz*ca;var rx2=rx1*cb+rz1*sb;var ry2=ry1;var rz2=rx1*(-sb)+rz1*cb;var rx3=rx2*cc+ry2*(-sc);var ry3=rx2*sc+ry2*cc;var rz3=rz2;mcList[j].cx=rx3;mcList[j].cy=ry3;mcList[j].cz=rz3;per=d/(d+rz3);mcList[j].x=(howElliptical*rx3*per)-(howElliptical*2);mcList[j].y=ry3*per;mcList[j].scale=per;mcList[j].alpha=per;mcList[j].alpha=(mcList[j].alpha-0.6)*(10/6)}doPosition();depthSort()}function depthSort(){var i=0;var aTmp=[];for(i=0;i<aA.length;i++){aTmp.push(aA[i])}aTmp.sort(function(vItem1,vItem2){if(vItem1.cz>vItem2.cz){return-1}else if(vItem1.cz<vItem2.cz){return 1}else{return 0}});for(i=0;i<aTmp.length;i++){aTmp[i].style.zIndex=i}}function positionAll(){var phi=0;var theta=0;var max=mcList.length;var i=0;var aTmp=[];var oFragment=document.createDocumentFragment();for(i=0;i<aA.length;i++){aTmp.push(aA[i])}aTmp.sort(function(){return Math.random()<0.5?1:-1});for(i=0;i<aTmp.length;i++){oFragment.appendChild(aTmp[i])}oDiv.appendChild(oFragment);for(var i=1;i<max+1;i++){if(distr){phi=Math.acos(-1+(2*i-1)/max);theta=Math.sqrt(max*Math.PI)*phi}else{phi=Math.random()*(Math.PI);theta=Math.random()*(2*Math.PI)}mcList[i-1].cx=radius*Math.cos(theta)*Math.sin(phi);mcList[i-1].cy=radius*Math.sin(theta)*Math.sin(phi);mcList[i-1].cz=radius*Math.cos(phi);aA[i-1].style.left=mcList[i-1].cx+oDiv.offsetWidth/2-mcList[i-1].offsetWidth/2+'px';aA[i-1].style.top=mcList[i-1].cy+oDiv.offsetHeight/2-mcList[i-1].offsetHeight/2+'px'}}function doPosition(){var l=oDiv.offsetWidth/2;var t=oDiv.offsetHeight/2;for(var i=0;i<mcList.length;i++){aA[i].style.left=mcList[i].cx+l-mcList[i].offsetWidth/2+'px';aA[i].style.top=mcList[i].cy+t-mcList[i].offsetHeight/2+'px';aA[i].style.fontSize=Math.ceil(12*mcList[i].scale/2)+80+'px';aA[i].style.filter="alpha(opacity="+100*mcList[i].alpha+")";aA[i].style.opacity=mcList[i].alpha}}function sineCosine(a,b,c){sa=Math.sin(a*dtr);ca=Math.cos(a*dtr);sb=Math.sin(b*dtr);cb=Math.cos(b*dtr);sc=Math.sin(c*dtr);cc=Math.cos(c*dtr)}</script>
<?php get_footer(); ?>