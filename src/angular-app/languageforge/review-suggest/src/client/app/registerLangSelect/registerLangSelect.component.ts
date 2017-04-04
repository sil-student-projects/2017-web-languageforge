import { Component, OnInit, ViewChild } from '@angular/core';
import { NgModel, FormBuilder, Validators } from '@angular/forms';
import { Router } from '@angular/router';

import { User } from '../shared/models/user';
declare var Materialize:any;

import { AuthService } from '../shared/services/auth.service';

//TEST

//import { Component } from '@angular/core';
import {FilterPipe} from './pipes'

@Component({
  selector: 'app-root',
  templateUrl: './app.component.html',
  styleUrls: ['./app.component.css']
})
export class AppComponent {
  title:String;
  names:any;
  constructor(){
    this.title = "Angular 2 simple search";
        this.names = ['Prashobh','Abraham','Anil','Sam','Natasha','Marry','Zian','karan']
  }
}

//END

@Component({
  moduleId: module.id,
  selector: 'logon',
  templateUrl: 'registerLangSelect.component.html',
})

export class RegisterLangSelectComponent implements OnInit {

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