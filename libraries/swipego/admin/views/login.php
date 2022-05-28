<div class="wrap"><h1></h1></div>

<div class="swipego-page">
    <div class="flex flex-col p-4">
        <div class="container px-4 mt-10 mx-auto md:px-10 max-w-lg bg-contain bg-app-logo bg-no-repeat bg-center">
            <img class="object-contain h-10 border-none m-auto mb-10" alt="<?php esc_attr_e( 'Swipe logo', 'swipego' ); ?>" src="<?php echo esc_attr( SWIPEGO_URL . 'assets/images/logo-swipe.svg' ); ?>">

            <ol class="items-center flex mb-5">
                <li class="w-1/2 relative">
                    <div class="flex items-center overflow-hidden">
                        <div class="absolute w-2 h-2 bg-zinc-100 rounded-full border border-primary inset-x-0 m-auto z-10"></div>
                        <div class="flex w-full bg-primary h-px absolute -top-px left-2/4"></div>
                    </div>
                    <div class="mt-3 sm:pr-8">
                        <span class="w-20 font-semibold text-md text-center sm:pl-5 m-auto block"><?php esc_html_e( 'Login Account', 'swipego' ); ?></span>
                    </div>
                </li>
                <li class="w-1/2 relative">
                    <div class="flex items-center overflow-hidden">
                        <div class="absolute w-2 h-2 bg-primary rounded-full border border-primary inset-x-0 m-auto z-10"></div>
                    </div>
                    <div class="mt-3 sm:pr-8">
                        <span class="w-20 font-semibold text-md text-center sm:pl-5 m-auto block"><?php esc_html_e( 'Accepting Payment', 'swipego' ); ?></span>
                    </div>
                </li>
            </ol>

            <div class="relative">
                <div class="mt-2 filter inset-1 blur-sm absolute rounded-md opacity-50 bg-purple-300">
            </div>

            <div class="absolute mt-2 filter inset-1 blur-lg rounded-lg bg-gray-300"></div>

            <div class="relative bg-white mx-auto rounded-lg md:p-10 p-8">
                <h3 class="mb-5 font-semibold text-md text-center"><?php esc_html_e( 'Login to your Swipe account', 'swipego' ); ?></h3>

                <form id="swipego-login" action="" method="POST">
                    <div class="space-y-5">
                        <div class="form-group flex flex-col">
                            <label for="email" class="block text-sm font-light text-gray-700"><?php esc_html_e( 'Email', 'swipego' ); ?></label>
                            <div class="mt-2">
                                <input type="email" id="email" name="email" autocomplete="email" class="rounded-md w-full h-10 shadow-sm text-sm focus:ring-primary border-none ring-1 focus:ring-2 focus:outline-none ring-bordercolor" required>
                            </div>
                        </div>
                        <div class="form-group flex flex-col">
                            <div class="relative">
                                <div class="flex flex-row justify-between">
                                    <label for="password" class="block text-sm font-light text-gray-700"><?php esc_html_e( 'Password', 'swipego' ); ?></label>
                                    <div class="flex justify-center space-x-1 text-sm">
                                        <a href="https://swipego.io/forgot-password" class="text-primary hover:text-purple-500 focus:shadow-none" target="_blank"><?php esc_html_e( 'Forgot Password?', 'swipego' ); ?></a>
                                    </div>
                                </div>
                                <input type="password" id="password" name="password" class="w-full mt-2 pr-10 h-10 rounded-md shadow-sm focus:ring-primary border-none ring-1 ring-bordercolor focus:ring-2 focus:outline-none text-sm ring-bordercolor" required>
                                <span class="toggle-password cursor-pointer absolute top-0 right-0 m-3 mt-10">
                                    <svg class="eye-close w-4 h-4" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M3.63309 2.64645C3.43783 2.45118 3.12125 2.45118 2.92599 2.64645C2.73073 2.84171 2.73073 3.15829 2.92599 3.35355L6.42481 6.85237C4.74993 7.99178 3.50288 9.77383 3.05965 11.8979C3.00325 12.1682 3.17666 12.4331 3.44698 12.4895C3.7173 12.5459 3.98216 12.3725 4.03857 12.1021C4.44238 10.167 5.60316 8.56289 7.14664 7.57421L8.72848 9.15604C7.84463 9.79133 7.26901 10.8284 7.26901 12C7.26901 13.933 8.83601 15.5 10.769 15.5C11.9406 15.5 12.9777 14.9244 13.613 14.0405L17.926 18.3536C18.1212 18.5488 18.4378 18.5488 18.6331 18.3536C18.8284 18.1583 18.8284 17.8417 18.6331 17.6464L3.63309 2.64645ZM10.9033 8.50253L14.2665 11.8657C14.1976 10.0395 12.7295 8.57143 10.9033 8.50253ZM8.31058 5.9098L9.11364 6.71286C9.65095 6.57353 10.2096 6.5 10.7795 6.5C13.9851 6.5 16.8369 8.82688 17.5204 12.1021C17.5768 12.3725 17.8417 12.5459 18.112 12.4895C18.3823 12.433 18.5557 12.1682 18.4993 11.8979C17.722 8.17312 14.4729 5.5 10.7795 5.5C9.92432 5.5 9.09296 5.64331 8.31058 5.9098Z" fill="#313335"></path></svg>
                                    <svg style="display: none;" class="eye-open w-4 h-4" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg" data-v-dd2e640e=""><path d="M4.03863 12.1021C4.72208 8.82689 7.57391 6.5 10.7796 6.5C13.9852 6.5 16.837 8.82688 17.5205 12.1021C17.5769 12.3725 17.8418 12.5459 18.1121 12.4895C18.3824 12.433 18.5558 12.1682 18.4994 11.8979C17.7221 8.17312 14.473 5.5 10.7796 5.5C7.08614 5.5 3.83696 8.17311 3.05972 11.8979C3.00331 12.1682 3.17672 12.433 3.44704 12.4895C3.71736 12.5459 3.98222 12.3725 4.03863 12.1021ZM10.7691 8.5C12.7021 8.5 14.2691 10.067 14.2691 12C14.2691 13.933 12.7021 15.5 10.7691 15.5C8.83607 15.5 7.26907 13.933 7.26907 12C7.26907 10.067 8.83607 8.5 10.7691 8.5Z" fill="#313335"></path></svg>
                                </span>
                            </div>
                        </div>

                        <span id="errors" class="space-x-1 block"></span>

                        <div class="flex justify-center">
                            <div class="inline-flex justify-center">
                                <input type="checkbox" id="remember" name="remember" class="mt-1 rounded shadow-sm focus:border-primary focus:ring-primary focus:ring-offset-0 focus:ring-1 border-bordercolor checked:border-primary text-primary focus:border-primary focus:ring-primary">
                                <label for="remember" class="ml-2 mt-1 text-sm"><?php esc_html_e( 'Stay signed in for a week', 'swipego' ); ?></label>
                            </div>
                        </div>
                        <button class="relative px-4 py-2 rounded-md inline-flex items-center justify-center disabled:opacity-50 border transition-all text-sm w-full bg-primary hover:bg-hover-button focus:bg-click-button text-white border-transparent ring-none" type="submit">
                            <svg class="w-8 h-4 absolute left-4 md:left-8 text-white absolute left-4 md:left-8 text-white" viewBox="0 0 26 16" fill="none" xmlns="http://www.w3.org/2000/svg"><g clip-path="url(#clip0)"><path d="M12.6413 15.3051H23.0799C23.829 15.3467 24.5655 15.0988 25.137 14.6126C25.7085 14.1264 26.0712 13.4391 26.15 12.693L27.807 3.29726C28.0994 1.63059 27.3294 0.685181 25.6627 0.685181H15.2144C14.4652 0.643508 13.7287 0.891423 13.1573 1.37763C12.5858 1.86383 12.2231 2.5511 12.1442 3.29726L10.4873 12.693C10.1949 14.3694 10.9649 15.3051 12.6413 15.3051Z" fill="#D3CBE4"></path><path d="M2.40736 15.3051H12.8362C13.5854 15.3467 14.3219 15.0988 14.8933 14.6126C15.4648 14.1264 15.8275 13.4391 15.9064 12.693L17.5633 3.29726C17.8654 1.63059 17.0858 0.685181 15.4191 0.685181H4.98047C4.23131 0.643508 3.49483 0.891423 2.92336 1.37763C2.35189 1.86383 1.98915 2.5511 1.91027 3.29726L0.253399 12.693C-0.0389987 14.3694 0.730944 15.3051 2.40736 15.3051Z" fill="#623F98"></path><path d="M27.076 7.62476H7.33917V8.38499H27.076V7.62476Z" fill="white"></path><path d="M25.6627 0.685181H4.98047C4.23131 0.643508 3.49483 0.891423 2.92336 1.37763C2.35189 1.86383 1.98915 2.5511 1.91027 3.29726L0.253399 12.693C-0.0389987 14.3694 0.730944 15.3051 2.40736 15.3051H23.0799C23.829 15.3467 24.5655 15.0988 25.137 14.6126C25.7085 14.1264 26.0712 13.4391 26.15 12.693L27.807 3.29726C28.0994 1.63059 27.3294 0.685181 25.6627 0.685181Z" stroke="white" stroke-width="0.39" stroke-miterlimit="10"></path></g><defs><clipPath id="clip0"><rect width="28.0604" height="15" fill="white" transform="translate(0 0.5)"></rect></clipPath></defs></svg>
                            <span id="submit-text"><?php esc_html_e( 'Sign In', 'swipego' ); ?></span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
