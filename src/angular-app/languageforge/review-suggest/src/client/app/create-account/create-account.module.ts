import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';
import { CreateAccountComponent } from './create-account.component';
import { CreateAccountRoutingModule } from './create-account-routing.module';

@NgModule({
  imports: [CommonModule, CreateAccountRoutingModule],
  declarations: [CreateAccountComponent],
  exports: [CreateAccountComponent]
})
export class CreateAccountModule { }
