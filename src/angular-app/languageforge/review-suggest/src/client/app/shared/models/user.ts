export class User {
  constructor(
    public id: string = '',
    public email: string = '',
    public username: string = '',
    public password: string = '',
    public confPass: string = ''
  ) { }
}