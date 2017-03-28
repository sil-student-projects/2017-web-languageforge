import { NgModule } from '@angular/core';
import { RouterModule } from '@angular/router';
import { RegisterLangSelectComponent } from './registerLangSelect.component';

@NgModule({
  imports: [
    RouterModule.forChild([
      { path: 'registerLangSelect', component: RegisterLangSelectComponent }
    ])
  ],
  exports: [RouterModule]
})
export class RegisterLangSelectRoutingModule { }