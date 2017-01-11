import { NgModule } from '@angular/core';
import { BrowserModule } from '@angular/platform-browser';
import { APP_BASE_HREF } from '@angular/common';
import { HttpModule } from '@angular/http';
import { AppComponent } from './app.component';
import { AppRoutingModule } from './app-routing.module';

import { AboutModule } from './about/about.module';
import { HomeModule } from './home/home.module';
import { SharedModule } from './shared/shared.module';
import { CreateAccountModule } from './create-account/create-account.module';
import { LoginModule } from './login/login.module';
import { ProjectSelectModule } from './project-select/project-select.module';
import { ReviewModule } from './review/review.module';

@NgModule({
  imports: [BrowserModule, HttpModule, AppRoutingModule, AboutModule, CreateAccountModule, HomeModule, LoginModule, ProjectSelectModule, ReviewModule, SharedModule.forRoot()],
  declarations: [AppComponent],
  providers: [{
    provide: APP_BASE_HREF,
    useValue: '<%= APP_BASE %>'
  }],
  bootstrap: [AppComponent]

})
export class AppModule { }
