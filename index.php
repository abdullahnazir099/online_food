<?php

include 'config.php';

session_start();

if (isset($_SESSION['user_id'])) {
   $user_id = $_SESSION['user_id'];
} else {
   $user_id = '';
}
;

if (isset($_POST['register'])) {

   $name = $_POST['name'];
   $name = filter_var($name, FILTER_SANITIZE_STRING);
   $email = $_POST['email'];
   $email = filter_var($email, FILTER_SANITIZE_STRING);
   $pass = sha1($_POST['pass']);
   $pass = filter_var($pass, FILTER_SANITIZE_STRING);
   $cpass = sha1($_POST['cpass']);
   $cpass = filter_var($cpass, FILTER_SANITIZE_STRING);

   $select_user = $conn->prepare("SELECT * FROM `user` WHERE name = ? AND email = ?");
   $select_user->execute([$name, $email]);

   if ($select_user->rowCount() > 0) {
      $message[] = 'username or email already exists!';
   } else {
      if ($pass != $cpass) {
         $message[] = 'confirm password not matched!';
      } else {
         $insert_user = $conn->prepare("INSERT INTO `user`(name, email, password) VALUES(?,?,?)");
         $insert_user->execute([$name, $email, $cpass]);
         $message[] = 'registered successfully, login now please!';
      }
   }

}

if (isset($_POST['update_qty'])) {
   $cart_id = $_POST['cart_id'];
   $qty = $_POST['qty'];
   $qty = filter_var($qty, FILTER_SANITIZE_STRING);
   $update_qty = $conn->prepare("UPDATE `cart` SET quantity = ? WHERE id = ?");
   $update_qty->execute([$qty, $cart_id]);
   $message[] = 'cart quantity updated!';
}

if (isset($_GET['delete_cart_item'])) {
   $delete_cart_id = $_GET['delete_cart_item'];
   $delete_cart_item = $conn->prepare("DELETE FROM `cart` WHERE id = ?");
   $delete_cart_item->execute([$delete_cart_id]);
   header('location:index.php');
}

if (isset($_GET['logout'])) {
   session_unset();
   session_destroy();
   header('location:index.php');
}

if (isset($_POST['add_to_cart'])) {

   if ($user_id == '') {
      $message[] = 'please login first!';
   } else {

      $pid = $_POST['pid'];
      $name = $_POST['name'];
      $price = $_POST['price'];
      $image = $_POST['image'];
      $qty = $_POST['qty'];
      $qty = filter_var($qty, FILTER_SANITIZE_STRING);

      $select_cart = $conn->prepare("SELECT * FROM `cart` WHERE user_id = ? AND name = ?");
      $select_cart->execute([$user_id, $name]);

      if ($select_cart->rowCount() > 0) {
         $message[] = 'already added to cart';
      } else {
         $insert_cart = $conn->prepare("INSERT INTO `cart`(user_id, pid, name, price, quantity, image) VALUES(?,?,?,?,?,?)");
         $insert_cart->execute([$user_id, $pid, $name, $price, $qty, $image]);
         $message[] = 'added to cart!';
      }

   }

}

if (isset($_POST['order'])) {

   if ($user_id == '') {
      $message[] = 'please login first!';
   } else {
      $name = $_POST['name'];
      $name = filter_var($name, FILTER_SANITIZE_STRING);
      $number = $_POST['number'];
      $number = filter_var($number, FILTER_SANITIZE_STRING);
      $address = 'flat no.' . $_POST['flat'] . ', ' . $_POST['street'] . ' - ' . $_POST['pin_code'];
      $address = filter_var($address, FILTER_SANITIZE_STRING);
      $method = $_POST['method'];
      $method = filter_var($method, FILTER_SANITIZE_STRING);
      $total_price = $_POST['total_price'];
      $total_products = $_POST['total_products'];

      $select_cart = $conn->prepare("SELECT * FROM `cart` WHERE user_id = ?");
      $select_cart->execute([$user_id]);

      if ($select_cart->rowCount() > 0) {
         $insert_order = $conn->prepare("INSERT INTO `orders`(user_id, name, number, method, address, total_products, total_price) VALUES(?,?,?,?,?,?,?)");
         $insert_order->execute([$user_id, $name, $number, $method, $address, $total_products, $total_price]);
         $delete_cart = $conn->prepare("DELETE FROM `cart` WHERE user_id = ?");
         $delete_cart->execute([$user_id]);
         $message[] = 'order placed successfully!';
      } else {
         $message[] = 'your cart empty!';
      }
   }

}

?>

<!DOCTYPE html>
<html lang="en">

<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Dabour</title>

   
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">

   <!-- custom css file link  -->
   <link rel="stylesheet" href="css/style.css">

</head>

<body>
   <?php include 'header.php' ?>

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

         <a href="#order" class="btn">order now</a>

      </section>

   </div>

   <div class="home-bg">

      <section class="home" id="home">

         <div class="slide-container">

            <div class="slide active">
               <div class="image">
                  <img src="images/rice.png" alt="">
               </div>
               <div class="content">
                  <h3>Mumbai Biryani</h3>
                  <div class="fas fa-angle-left" onclick="prev()"></div>
                  <div class="fas fa-angle-right" onclick="next()"></div>
               </div>
            </div>

            <div class="slide">
               <div class="image">
                  <img src="images/grill.png" alt="">
               </div>
               <div class="content">
                  <h3>Grill Chicken</h3>
                  <div class="fas fa-angle-left" onclick="prev()"></div>
                  <div class="fas fa-angle-right" onclick="next()"></div>
               </div>
            </div>

            <div class="slide">
               <div class="image">
                  <img src="images/tanduri-chicken.png" alt="">
               </div>
               <div class="content">
                  <h3>Tanduri Chicken</h3>
                  <div class="fas fa-angle-left" onclick="prev()"></div>
                  <div class="fas fa-angle-right" onclick="next()"></div>
               </div>
            </div>

         </div>

      </section>

   </div>


   <!-- about section starts  -->


   <section class="about" id="about">

      <h1 class="heading">about us</h1>


      <div class="box-container">

         <div class="box">
            <img src="images/about-pic.jpg" alt="">
            <h3>made with love</h3>
            <p>Every dish, made with love and a sprinkle of passion, just for you.
               Crafted with care and a dash of love, because great food starts from the heart.</p>

         </div>

         <div class="box">
            <img src="images/about-pic-2.avif" alt="">
            <h3>30 minutes delivery</h3>
            <p>Delicious meals at your door in just 30 minutes—fast, fresh, and ready to savor.
               Hungry? We've got you covered! Enjoy quick 30-minute delivery for a taste of instant satisfaction.</p>

         </div>

         <div class="box">
            <img src="images/about-pic-3.jpg" alt="">
            <h3> share with freinds</h3>
            <p>Spread the joy! Share our flavors with friends for unforgettable moments together.
               Good food is best enjoyed with good company. Share the love—and the dishes—with your friends today!</p>

         </div>

      </div>

   </section>

   <!-- about section ends -->

 

   <section id="testimonials">
    
      <h1 class="heading">What Our Customers Say</h1>
      <div class="testimonial-container container">
         <div class="testimonial-box">
            <div class="customer-detail">
               <div class="customer-photo">
                  <img src="https://i.postimg.cc/5Nrw360Y/male-photo1.jpg" alt="" />
                  <p class="customer-name">Ross Lee</p>
               </div>
            </div>
            <div class="star-rating">
               <span class="fa fa-star checked"></span>
               <span class="fa fa-star checked"></span>
               <span class="fa fa-star checked"></span>
               <span class="fa fa-star checked"></span>
               <span class="fa fa-star checked"></span>
            </div>
            <p class="testimonial-text">
            "Had a party catered by them, and it was a hit! Everyone raved about the food. Thank you for making our event special!"
            </p>

         </div>
         <div class="testimonial-box">
            <div class="customer-detail">
               <div class="customer-photo">
                  <img src="https://i.postimg.cc/sxd2xCD2/female-photo1.jpg" alt="" />
                  <p class="customer-name">Amelia Watson</p>
               </div>
            </div>
            <div class="star-rating">
               <span class="fa fa-star checked"></span>
               <span class="fa fa-star checked"></span>
               <span class="fa fa-star checked"></span>
               <span class="fa fa-star checked"></span>
               <span class="fa fa-star checked"></span>
            </div>
            <p class="testimonial-text">
            "Impressed with the variety and freshness of the dishes. Perfect for busy weeknights when I want something tasty and convenient!"
            </p>

         </div>
         <div class="testimonial-box">
            <div class="customer-detail">
               <div class="customer-photo">
                  <img src="https://i.postimg.cc/fy90qvkV/male-photo3.jpg" alt="" />
                  <p class="customer-name">Ben Roy</p>
               </div>
            </div>
            <div class="star-rating">
               <span class="fa fa-star checked"></span>
               <span class="fa fa-star checked"></span>
               <span class="fa fa-star checked"></span>
               <span class="fa fa-star checked"></span>
               <span class="fa fa-star checked"></span>
            </div>
            <p class="testimonial-text">
            "Absolutely delicious! The flavors were amazing, and the delivery was right on time. Will definitely be ordering again!"
            </p>

         </div>
      </div>
   </section>


   <!-- faq section starts  -->

   <section class="faq" id="faq">

      <h1 class="heading">FAQ</h1>
      <!-- <h2 class="testimonial-title">FAQ</h2> -->

      <div class="accordion-container">

         <div class="accordion active">
            <div class="accordion-heading">
               <span>How does the ordering process work?</span>
               <i class="fas fa-angle-down"></i>
            </div>
            <p class="accrodion-content">
               Ordering with us is quick and easy! Simply browse our menu of mouthwatering dishes, select your
               favorites, and add them to your cart. Once you're satisfied with your selections, proceed to checkout.
               Here, you'll provide your delivery details and choose a convenient payment method. After confirming your
               order, sit back and relax as we prepare your delicious meal with love and care. You'll receive updates on
               your order status, and before you know it, your delectable feast will be at your doorstep!
            </p>
         </div>

         <div class="accordion">
            <div class="accordion-heading">
               <span> What is the estimated delivery time?</span>
               <i class="fas fa-angle-down"></i>
            </div>
            <p class="accrodion-content">
               We understand the excitement of receiving your delicious meal promptly! Our estimated delivery time
               typically ranges between 30 to 45 minutes, depending on your location and order volume. Rest assured, we
               strive to get your food to you as swiftly as possible without compromising on quality. You can track your
               order in real-time and anticipate the delightful moment when your meal arrives at your doorstep.
            </p>
         </div>

         <div class="accordion">
            <div class="accordion-heading">
               <span> Do you cater to large gatherings or events?</span>
               <i class="fas fa-angle-down"></i>
            </div>
            <p class="accrodion-content">
               Absolutely! We specialize in catering to all sizes of gatherings, whether it's an intimate gathering with
               friends or a grand celebration. Our diverse menu offers a wide selection of dishes to satisfy everyone's
               taste buds. Simply give us a call at [phone number] or email us at [email] to discuss your event details,
               dietary preferences, and desired menu options. Our dedicated team will work closely with you to create a
               customized culinary experience that will leave your guests raving!
            </p>
         </div>

         <div class="accordion">
            <div class="accordion-heading">
               <span>Can I know the protein content of your dishes?</span>
               <i class="fas fa-angle-down"></i>
            </div>
            <p class="accrodion-content">
               Absolutely! We understand the importance of protein in a balanced diet. The protein content of each dish
               varies, and you can find detailed nutritional information on our menu. Look for the protein symbol next
               to the dish name or click on the dish for a full breakdown of its nutritional values. Whether you're a
               fitness enthusiast or simply conscious of your protein intake, we've got the information you need to make
               informed choices about your meal.
            </p>
         </div>


         <div class="accordion">
            <div class="accordion-heading">
               <span>How are your dishes prepared?</span>
               <i class="fas fa-angle-down"></i>
            </div>
            <p class="accrodion-content">
               Our dishes are expertly prepared using a combination of cooking methods to bring out the best flavors.
               While some dishes are grilled or baked for a healthier touch, others may be cooked with a minimal amount
               of high-quality oils or fats. We prioritize both taste and health, ensuring that each dish is cooked to
               perfection while maintaining its nutritional value. For specific cooking methods and ingredients used,
               feel free to check the individual dish descriptions on our menu.
            </p>
         </div>

      </div>

   </section>




   <!-- faq section ends -->




   <!-- footer section -->
   <h1 class="heading">contact</h1>

   <section class="footer">

      <div class="box-container">

         <div class="box">
            <i class="fas fa-phone"></i>
            <h3>phone number</h3>
            <p>+123-456-7890</p>

         </div>

         <div class="box">
            <i class="fas fa-map-marker-alt"></i>
            <h3>our address</h3>
            <p>Address</p>
         </div>

         <div class="box">
            <i class="fas fa-clock"></i>
            <h3>opening hours</h3>
            <p>00:09am to 00:10pm</p>
         </div>

         <div class="box">
            <i class="fas fa-envelope"></i>
            <h3>email address</h3>
            <p>dabour@gmail.com</p>

         </div>

      </div>


   </section>

   <!-- footer section ends -->



   <?php include 'footer.php' ?>




   <!-- custom js file link  -->
   <script src="js/script.js"></script>

</body>

</html>