import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';
import { CreateAccountComponent } from './create-account.component';
import { CreateAccountRoutingModule } from './create-account-routing.module';
import { SharedModule } from '../shared/shared.module';
import { NameListService } from '../shared/name-list/name-list.service';

@NgModule({
  imports: [CommonModule, CreateAccountRoutingModule, SharedModule],
  declarations: [CreateAccountComponent],
  exports: [CreateAccountComponent],
  providers: [NameListService]
})
export class CreateAccountModule { }