<?php
/**
 * The template for displaying comments
 *
 * This is the template that displays the area of the page that contains both the current comments
 * and the comment form.
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/
 *
 * @package Ecoveles
 */

/*
 * If the current post is protected by a password and
 * the visitor has not yet entered the password we will
 * return early without loading the comments.
 */

if ( post_password_required() ) {
	return;
}
?>
<div x-data="{comments: [], page:1, showCommentModal:false, isLoading:false}"
     x-init="Alpine.store('FS').getProductComments(<?php the_ID(); ?>,page).then(c=>comments=c.data)">
    <div class="flex xl:flex-row flex-col xl:items-center justify-between xl:gap-6 gap-3 mb-3">
        <div class="xl:text-[32px] text-[22px] xl:leading-[42px] leading-[28px]"><?php _e( 'Відгуки', 'f-shop' ) ?></div>
        <button x-on:click.prevent="showCommentModal=true"
                class="flex items-center justify-center py-[10px] px-[24px] text-[17px] leading-[30px] font-medium font-montserrat  bg-theme6 text-white rounded-[30px] h-[56px] gap-2">
			<?php _e( 'Leave a review', 'f-shop' ) ?>
        </button>
    </div>

    <div class="flex justify-between bg-theme8 px-6 py-3 rounded-[24px] leading-[32px text-1">
        <div class="text-center">
            <div class="xl:text-[32px] text-[22px] xl:leading-[42px] leading-[28px]"><?php fs_product_average_rating(); ?></div>
            <div class="xl:text-[18px] text-[12px] leading-[16px]"><?php _e( 'average score', 'f-shop' ) ?></div>
        </div>
        <div class="text-center">
            <div class="text-[18px] mb-[2px]"><?php fs_comments_count(); ?></div>
            <div class="flex gap-3">
				<?php fs_product_rating(); ?>
            </div>
        </div>
    </div>

    <div class="h-[1px] bg-[#CDCDCD] mt-5 mb-6"></div>

    <div class="grid gap-6">
        <template x-for="comment in comments">
            <div class="border border-solid border-theme-gray p-6 grid gap-3 rounded-[12px]">
                <div class="flex justify-between ">
                    <div class="text-[18px] leading-[32px]" x-text="comment.comment_author"></div>
                    <div class="text-right ">
                        <div class="text-[14px] mb-[5px]" x-text="comment.date"></div>
                        <div class="flex gap-1">
                            <template x-for="i in 5">
                                <svg width="16.000000" height="16.000000" viewBox="0 0 16 16" fill="none"
                                     xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink">
                                    <defs>
                                        <clipPath id="clip129_8730">
                                            <rect id="rating-star" width="16.000000" height="16.000000" fill="white"
                                                  fill-opacity="0"/>
                                        </clipPath>
                                    </defs>
                                    <rect id="rating-star" width="16.000000" height="16.000000" fill="#FFFFFF"
                                          fill-opacity="0"/>
                                    <g clip-path="url(#clip129_8730)">
                                        <path id="path1620"
                                              d="M8.45 2.93L9.77 5.66C9.84 5.81 9.98 5.92 10.14 5.94L13.04 6.38C13.44 6.45 13.61 6.93 13.33 7.23L11.22 9.41C11.11 9.53 11.06 9.68 11.09 9.84L11.6 12.97C11.67 13.38 11.23 13.69 10.86 13.49L8.24 12.01C8.09 11.93 7.9 11.93 7.75 12.01L5.13 13.49C4.76 13.69 4.32 13.38 4.39 12.97L4.9 9.84C4.93 9.68 4.88 9.53 4.77 9.41L2.66 7.23C2.38 6.93 2.55 6.45 2.95 6.38L5.85 5.94C6.01 5.92 6.15 5.81 6.22 5.66L7.54 2.93C7.73 2.55 8.26 2.55 8.45 2.93Z"
                                              fill="#FFB91D" fill-opacity="1.000000" fill-rule="nonzero"/>
                                        <path id="path1620" :fill="comment.comment_karma>=i ? '#FFB91D' : '#FFFFFF'"
                                              d="M9.77 5.66C9.84 5.81 9.98 5.92 10.14 5.94L13.04 6.38C13.44 6.45 13.61 6.93 13.33 7.23L11.22 9.41C11.11 9.53 11.06 9.68 11.09 9.84L11.6 12.97C11.67 13.38 11.23 13.69 10.86 13.49L8.24 12.01C8.09 11.93 7.9 11.93 7.75 12.01L5.13 13.49C4.76 13.69 4.32 13.38 4.39 12.97L4.9 9.84C4.93 9.68 4.88 9.53 4.77 9.41L2.66 7.23C2.38 6.93 2.55 6.45 2.95 6.38L5.85 5.94C6.01 5.92 6.15 5.81 6.22 5.66L7.54 2.93C7.73 2.55 8.26 2.55 8.45 2.93L9.77 5.66Z"
                                              stroke="#FFB91D" stroke-opacity="1.000000" stroke-width="1.200000"/>
                                    </g>
                                </svg>
                            </template>

                        </div>
                    </div>
                </div>
                <p class="leading-[26px] text-black" x-text="comment.comment_content"></p>
                <div x-show="typeof comment.images === 'object'" class="flex flex-wrap gap-2">
                    <template x-for="img in comment.images">
                        <a :href="img" target="_blank" class="flex">
                            <img :src="img"
                                 class="rounded-[12px] w-[100px] h-[100px] object-cover" alt="">
                        </a>
                    </template>
                </div>
                <div class="flex items-center gap-3">
                    <button x-on:click.prevent="Alpine.store('FS').commentLikeDislike(comment.comment_ID,'like').then(c=>comment.likes=c.data.likes)"
                            class="flex items-center gap-2 text-[14px] text-theme5">
                        <img src="<?php echo get_template_directory_uri() ?>/img/icons/like.svg" alt=""> <span
                                x-text="comment.likes">0</span>
                    </button>
                    <button
                            x-on:click.prevent="Alpine.store('FS').commentLikeDislike(comment.comment_ID,'dislike').then(c=>comment.dislikes=c.data.dislikes)"
                            class="flex items-center gap-2 text-[14px]">
                        <img src="<?php echo get_template_directory_uri() ?>/img/icons/dislike.svg" alt="">
                        <span x-text="comment.dislikes">0</span>
                    </button>
                </div>
            </div>
        </template>
    </div>

    <button x-show="comments.length"
            x-on:click.prevent="isLoading=true;page++;Alpine.store('FS').getProductComments(<?php the_ID(); ?>,page).then(c=>{comments=c.data;isLoading=false;})"
            class="mt-6 bg-theme6 text-[17px] font-medium font-montserrat flex items-center gap-3 px-6 py-[10px] rounded-[30px] leading-[30px] text-white min-h-[56px]">
        <img x-show="isLoading" class="animate-spin"
             src="<?php echo get_template_directory_uri() ?>/img/icons/preloader.svg" alt="">
		<?php _e( 'Show more', 'f-shop' ) ?>
    </button>

    <!--Modal-->
    <div x-show="showCommentModal" style="display: none;"
         class="bg-black bg-opacity-80 w-full h-full fixed top-0 left-0 flex items-center justify-center overflow-auto z-[99999999]">
        <div class="flex flex-col w-[800px] max-w-[90%] p-[30px] bg-white shadow rounded-xl relative"
             x-on:click.outside="showCommentModal = false;">
            <button class="w-6 h-6 flex items-center justify-center absolute top-3 right-3"
                    x-on:click.prevent="showCommentModal = false">
                <img src="/wp-content/themes/roov/img/icons/modal-close.svg" alt="">
            </button>
            <form action=""
                  x-data="{comment: {name:'',email: '',body: '',rating:5 }, sendMessage: '' ,errors: [], files:[] }"
                  x-on:submit.prevent="errors=[];Alpine.store('FS').sendProductComment(<?php the_ID(); ?>,comment, files).then((r)=>{
                       if(r.success===false){
                          errors=r.data;
                       }else{
                          sendMessage=r.data.message;
                          comment={name:'',email: '',body: '',rating:5 };
                          files=[]
                       }
                  });"
                  class="flex flex-col gap-5">

                <div class="text-[34px] leading-[44px]"><?php _e( 'Leave feedback', 'f-shop' ); ?></div>

                <div class="grid grid-cols-2 gap-5">
                    <div class="flex flex-col gap-1.5">
                        <label for="comment-name"
                               class="text-[15px] leading-[20px] text-theme1"><?php _e( 'Name', 'f-shop' ); ?>
                            <sup>*</sup></label>
                        <input x-model="comment.name" type="text" id="comment-name"
                               :class="{'border-theme-red':errors.name }"
                               class="w-full border border-solid border-theme-gray rounded-[30px] p-3 h-[56px]">
                    </div>
                    <div class="flex flex-col gap-1.5">
                        <label for="comment-email"
                               class="text-[15px] leading-[20px] text-theme1"><?php _e( 'Email', 'f-shop' ) ?>
                            <sup>*</sup></label>
                        <input x-model="comment.email" type="email" id="comment-email"
                               :class="{'border-theme-red':errors.email }"
                               class="w-full border border-solid border-theme-gray rounded-[30px] p-3 h-[56px]">
                    </div>
                </div>

                <div class="flex flex-col gap-1.5">
                    <label for="comment-text"
                           class="text-[15px] leading-[20px] text-theme1"><?php _e( 'Review', 'f-shop' ) ?> <sup>*</sup></label>
                    <textarea x-model="comment.body" id="comment-text"
                              :class="{'border-theme-red':errors.body }"
                              class="w-full border border-solid border-theme-gray rounded-[30px] p-3 h-[107px]"
                    ></textarea>
                </div>

                <div class="flex flex-col gap-1.5">
                    <label for="comment-text"
                           class="text-[15px] leading-[20px] text-theme1"><?php _e( 'Rate the product', 'f-shop' ) ?>
                        <sup>*</sup></label>
                    <div class="flex items-center gap-1.5">
                        <template x-for="i in 5">
                            <svg x-on:click="comment.rating=i" class="hover:cursor-pointer" width="50.000000"
                                 height="50.000000" viewBox="0 0 50 50" fill="none" xmlns="http://www.w3.org/2000/svg"
                                 xmlns:xlink="http://www.w3.org/1999/xlink">
                                <defs>
                                    <clipPath id="clip5586_58166">
                                        <rect id="rating-star" width="50.000000" height="50.000000" fill="white"
                                              fill-opacity="0"/>
                                    </clipPath>
                                </defs>
                                <rect id="rating-star" width="50.000000" height="50.000000" fill="#FFFFFF"
                                      fill-opacity="0"/>
                                <g clip-path="url(#clip5586_58166)">
                                    <path id="path1620" x-bind:fill="comment.rating>=i?  '#FFB91D': '#FFFFFF'"
                                          d="M32.11 17.25C32.25 17.55 32.53 17.75 32.86 17.8L45.09 19.62C45.9 19.74 46.23 20.73 45.65 21.31L36.87 30.18C36.64 30.4 36.54 30.73 36.59 31.05L38.74 43.8C38.88 44.62 38 45.24 37.27 44.84L26.08 38.72C25.78 38.56 25.42 38.56 25.12 38.72L13.93 44.84C13.2 45.24 12.33 44.62 12.47 43.8L14.61 31.05C14.67 30.73 14.56 30.4 14.34 30.18L5.55 21.31C4.98 20.73 5.3 19.74 6.12 19.62L18.35 17.8C18.67 17.75 18.95 17.55 19.1 17.25L24.71 5.96C25.07 5.22 26.13 5.22 26.5 5.96L32.11 17.25Z"
                                          stroke="#FFB91D" stroke-opacity="1.000000" stroke-width="1.200000"/>
                                </g>
                            </svg>
                        </template>
                    </div>
                </div>
                <label class="border border-dashed border-theme5 p-3 rounded-[100px] hover:cursor-pointer flex items-center justify-center">
                    <input type="file" class="hidden" multiple accept="image/*"
                           x-on:change="files=$event.target.files">

                    <span class="flex justify-center items-center gap-4">
                        <svg width="24.000000" height="24.000000" viewBox="0 0 24 24" fill="none"
                             xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink"><defs><clipPath
                                        id="clip129_5656"><rect id="icons" width="24.000000" height="24.000000"
                                                                fill="white" fill-opacity="0"/></clipPath></defs><rect
                                    id="icons" width="24.000000" height="24.000000" fill="#FFFFFF" fill-opacity="0"/><g
                                    clip-path="url(#clip129_5656)"><path id="Vector"
                                                                         d="M11.33 0.4L11.33 1L11.33 1.02L11.33 17.44L6.52 12.63L6.08 12.2L5.23 13.04L11.93 19.74L18.19 13.48L18.2 13.47L18.62 13.04L17.78 12.2L17.35 12.62L17.34 12.63L12.53 17.44L12.53 1.02L12.53 1L12.53 0.4L11.33 0.4ZM23.86 22.4L0 22.4L0 23.6L23.86 23.6L23.86 22.4Z"
                                                                         fill="#DA984A" fill-opacity="1.000000"
                                                                         fill-rule="evenodd"/></g></svg>

                        <span class="flex flex-col">
                           <span class="text-[18px] leading-[32px] text-theme1"><?php _e( 'Attach an image', 'f-shop' ) ?></span>
                            <span class="text-sm leading-5 text-theme3">(<?php _e( 'The maximum file size is 10 Mb', 'f-shop' ) ?>)</span>
                        </span>

                    </span>


                </label>
                <p class="text-sm leading-5 text-theme3">
					<?php _e( 'Messages are moderated. Comments containing profanity, insults, links to third-party resources, spam will not be published.', 'f-shop' ) ?>
                </p>
                <div x-show="sendMessage" x-text="sendMessage"
                     class="p-3 border border-solid border-theme-green-light text-theme-green-light"></div>
                <button type="submit"
                        class="bg-theme6 text-[17px] font-medium font-montserrat flex justify-center items-center gap-3 px-6 py-[10px] rounded-[30px] leading-[30px] text-white min-h-[56px]">
					<?php _e( 'Send', 'f-shop' ); ?>
                </button>
            </form>

        </div>
    </div>
</div>