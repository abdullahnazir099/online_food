<?php
   if (isset($message)) {
      foreach ($message as $message) {
         echo '
         <div class="message">
            <span>' . $message . '</span>
            <i class="fas fa-times" onclick="this.parentElement.remove();"></i>
         </div>
         ';
      }
   }
   ?>

   <!-- header section starts  -->

   <header class="header">

      <section class="flex">

         <a href="#home" class="logo"><span>D</span>abur.</a>
         <!-- <a href="#home" class="logo"> <img src="images/home-img-1.png" alt=""></a> -->

         <nav class="navbar">
            <a href="index.php">home</a>
          
            <a href="menu.php">menu</a>
            <a href="order.php">order</a>
            <a href="contact.php">contact</a>
           
        
         </nav>

         <div class="icons">
            <div id="menu-btn" class="fas fa-bars"></div>
            <div id="user-btn" class="fas fa-user"></div>
            <div id="order-btn" class="fas fa-box"></div>
            <?php
            $count_cart_items = $conn->prepare("SELECT * FROM `cart` WHERE user_id = ?");
            $count_cart_items->execute([$user_id]);
            $total_cart_items = $count_cart_items->rowCount();
            ?>
            <div id="cart-btn" class="fas fa-shopping-cart"><span>(
                  <?= $total_cart_items; ?>)
               </span></div>
         </div>
         <div class="user">
            <?php
            $select_user = $conn->prepare("SELECT * FROM `user` WHERE id = ?");
            $select_user->execute([$user_id]);
            if ($select_user->rowCount() > 0) {
               while ($fetch_user = $select_user->fetch(PDO::FETCH_ASSOC)) {
                  echo '<h2>welcome ! <span>' . $fetch_user['name'] . '</span></h2>';
               
               }
            } else {
               echo ',<h2><p><span>you are not logged in now!</span></p></h>';
            }
            ?>
         </div>

      </section>

   </header>

   <!-- header section ends -->

   <div class="user-account">

      <section>

         <div id="close-account"><span>close</span></div>

         <div class="user">
            <?php
            $select_user = $conn->prepare("SELECT * FROM `user` WHERE id = ?");
            $select_user->execute([$user_id]);
            if ($select_user->rowCount() > 0) {
               while ($fetch_user = $select_user->fetch(PDO::FETCH_ASSOC)) {
                  echo '<p>welcome ! <span>' . $fetch_user['name'] . '</span></p>';
                  echo '<a href="index.php?logout" class="btn">logout</a>';
               }
            } else {
               echo '<p><span>you are not logged in now!</span></p>';
            }
            ?>
         </div>

          

         <div class="flex">

            <form action="user_login.php" method="post">
               <h3>login now</h3>
               <input type="email" name="email" required class="box" placeholder="enter your email" maxlength="50">
               <input type="password" name="pass" required class="box" placeholder="enter your password" maxlength="20">
               <input type="submit" value="login now" name="login" class="btn">
            </form>

            <form action="" method="post">
               <h3>register now</h3>
               <input type="text" name="name" oninput="this.value = this.value.replace(/\s/g, '')" required class="box"
                  placeholder="enter your username" maxlength="20">
               <input type="email" name="email" required class="box" placeholder="enter your email" maxlength="50">
               <input type="password" name="pass" required class="box" placeholder="enter your password" maxlength="20"
                  oninput="this.value = this.value.replace(/\s/g, '')">
               <input type="password" name="cpass" required class="box" placeholder="confirm your password"
                  maxlength="20" oninput="this.value = this.value.replace(/\s/g, '')">
               <input type="submit" value="register now" name="register" class="btn">
            </form>

         </div>

      </section>

   </div>

   <div class="my-orders">

      <section>

         <div id="close-orders"><span>close</span></div>

         <h3 class="title"> my orders </h3>

         <?php
         $select_orders = $conn->prepare("SELECT * FROM `orders` WHERE user_id = ?");
         $select_orders->execute([$user_id]);
         if ($select_orders->rowCount() > 0) {
            while ($fetch_orders = $select_orders->fetch(PDO::FETCH_ASSOC)) {
               ?>
               <div class="box">
                  <p> placed on : <span>
                        <?= $fetch_orders['placed_on']; ?>
                     </span> </p>
                  <p> name : <span>
                        <?= $fetch_orders['name']; ?>
                     </span> </p>
                  <p> number : <span>
                        <?= $fetch_orders['number']; ?>
                     </span> </p>
                  <p> address : <span>
                        <?= $fetch_orders['address']; ?>
                     </span> </p>
                  <p> payment method : <span>
                        <?= $fetch_orders['method']; ?>
                     </span> </p>
                  <p> total_orders : <span>
                        <?= $fetch_orders['total_products']; ?>
                     </span> </p>
                  <p> total price : <span>$
                        <?= $fetch_orders['total_price']; ?>/-
                     </span> </p>
                  <p> payment status : <span style="color:<?php if ($fetch_orders['payment_status'] == 'pending') {
                     echo 'red';
                  } else {
                     echo 'green';
                  }
                  ; ?>">
                        <?= $fetch_orders['payment_status']; ?>
                     </span> </p>
               </div>
               <?php
            }
         } else {
            echo '<p class="empty">nothing ordered yet!</p>';
         }
         ?>

      </section>

   </div>

   <div class="shopping-cart">

      <section>

         <div id="close-cart"><span>close</span></div>

         <?php
         $grand_total = 0;
         $select_cart = $conn->prepare("SELECT * FROM `cart` WHERE user_id = ?");
         $select_cart->execute([$user_id]);
         if ($select_cart->rowCount() > 0) {
            while ($fetch_cart = $select_cart->fetch(PDO::FETCH_ASSOC)) {
               $sub_total = ($fetch_cart['price'] * $fetch_cart['quantity']);
               $grand_total += $sub_total;
               ?>
               <div class="box">
                  <a href="index.php?delete_cart_item=<?= $fetch_cart['id']; ?>" class="fas fa-times"
                     onclick="return confirm('delete this cart item?');"></a>
                  <img src="uploaded_img/<?= $fetch_cart['image']; ?>" alt="">
                  <div class="content">
                     <p>
                        <?= $fetch_cart['name']; ?> <span>(
                           <?= $fetch_cart['price']; ?> x
                           <?= $fetch_cart['quantity']; ?>)
                        </span>
                     </p>
                     <form action="" method="post">
                        <input type="hidden" name="cart_id" value="<?= $fetch_cart['id']; ?>">
                        <input type="number" name="qty" class="qty" min="1" max="99" value="<?= $fetch_cart['quantity']; ?>"
                           onkeypress="if(this.value.length == 2) return false;">
                        <button type="submit" class="fas fa-edit" name="update_qty"></button>
                     </form>
                  </div>
               </div>
               <?php
            }
         } else {
            echo '<p class="empty"><span>your cart is empty!</span></p>';
         }
         ?>

         <div class="cart-total"> grand total : <span>$
               <?= $grand_total; ?>/-
            </span></div>

         <a href="order.php" class="btn">order now</a>

      </section>

   </div>