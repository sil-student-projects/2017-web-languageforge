import { NgModule } from '@angular/core';
import { RouterModule } from '@angular/router';
import { LoginComponent } from './login-routing.component';

@NgModule({
  imports: [
    RouterModule.forChild([
      { path: 'login-routing', component: LoginComponent }
    ])
  ],
  exports: [RouterModule]
})
export class LoginRoutingModule { }
