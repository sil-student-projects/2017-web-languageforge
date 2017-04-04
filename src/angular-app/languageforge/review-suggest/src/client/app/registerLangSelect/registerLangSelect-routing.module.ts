import { NgModule } from '@angular/core';
import { RouterModule } from '@angular/router';
import { RegisterLangSelectComponent } from './registerLangSelect.component';

//TEST

import { BrowserModule } from '@angular/platform-browser';
//import { NgModule } from '@angular/core';
import { FormsModule } from '@angular/forms';
import { HttpModule } from '@angular/http';

import {FilterPipe} from './pipes'

//import { AppComponent } from './app.component';

@NgModule({
  declarations: [
    RegisterLangSelectComponent,FilterPipe
  ],
  imports: [
    BrowserModule,
    FormsModule,
    HttpModule
  ],
  providers: [],
  bootstrap: [RegisterLangSelectComponent]
})
export class AppModule { }

//END

@NgModule({
  imports: [
    RouterModule.forChild([
      { path: 'registerLangSelect', component: RegisterLangSelectComponent }
    ])
  ],
  exports: [RouterModule]
})
export class RegisterLangSelectRoutingModule { }