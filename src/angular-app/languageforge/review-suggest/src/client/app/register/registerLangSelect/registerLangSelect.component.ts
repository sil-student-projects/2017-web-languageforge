import { Component, OnInit, ViewChild } from '@angular/core';
import { NgModel, FormBuilder, Validators } from '@angular/forms';
import { Router } from '@angular/router';

import { User } from '../../shared/models/user';
declare var Materialize:any;

import { AuthService } from '../../shared/services/auth.service';

@Component({
  moduleId: module.id,
  selector: 'logon',
  templateUrl: 'register.component.html',
})
export class RegisterComponent implements OnInit {

  private registerUser: User = new User();
  private authForm: NgModel;
  @ViewChild('registerForm') currentForm: NgModel;

  constructor(
    private router: Router,
    public authService: AuthService
  ) { } 

  ngOnInit(): void {
    if (this.authService.isLoggedIn()) {
      this.goToDashboard();
    }
  }

  goToDashboard() {
      this.router.navigate(['dashboard']);
  }

  onSubmit() {

    //MY TEST CODE
    if(this.registerUser.password == this.registerUser.confPass) {

        var toastContent = '<b>Success! Passwords match</b>';
        Materialize.toast(toastContent, 5000, 'green');

        this.authService.register(this.registerUser.email, this.registerUser.username, this.registerUser.password).subscribe(response => {
            if (response) {
                this.goToDashboard(); //maybe change this to swap you to log in page later
            } else {
                var toastContent = '<b>Error!</b>';
                Materialize.toast(toastContent, 5000, 'red');
              }
            });

    } else {
        var toastContent = '<b>Error! Passwords do NOT match!</b>';
        Materialize.toast(toastContent, 5000, 'red');
      }
    }

}